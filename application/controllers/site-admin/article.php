<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * PHP version 5
 * 
 * @package agni cms
 * @author vee w.
 * @license http://www.opensource.org/licenses/GPL-3.0
 *
 */

class article extends admin_controller {

	
	function __construct() {
		parent::__construct();
		// load model
		$this->load->model( array( 'posts_model', 'taxonomy_model' ) );
		// load helper
		$this->load->helper( array( 'category', 'date', 'form' ) );
		// load language
		$this->lang->load( 'post' );
		// set post_type
		$this->posts_model->post_type = 'article';
	}// __construct
	
	
	function _define_permission() {
		return array( 
				'post_article_perm' => 
					array( 
						'post_article_viewall_perm', 
						'post_article_add_perm', 
						'post_article_publish_unpublish_perm', 
						'post_article_edit_own_perm', 
						'post_article_edit_other_perm', 
						'post_article_delete_own_perm', 
						'post_article_delete_other_perm', 
						'post_article_sort_perm',
						'post_revert_revision',
						'post_delete_revision'
					) 
			);
	}// _define_permission
	
	
	function add() {
		// check permission
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_add_perm' ) != true ) {redirect( 'site-admin' );}
		// list themes for select
		$output['list_theme'] = $this->themes_model->list_enabled_themes();
		// list categories for check
		$this->taxonomy_model->tax_type = 'category';
		$output['list_category'] = $this->taxonomy_model->list_item();
		// preset settings and values
		$output['post_comment'] = '1';
		$output['post_status'] = '1';
		// save action
		if ( $this->input->post() ) {
			$data['tid'] = $this->input->post( 'tid' );// categories
			$data['tagid'] = $this->input->post( 'tagid' );// tags
			$data['theme_system_name'] = trim( $this->input->post( 'theme_system_name' ) );
				$data['theme_system_name'] = ( $data['theme_system_name'] == null ? null : $data['theme_system_name'] );
			$data['post_name'] = htmlspecialchars( trim( $this->input->post( 'post_name' ) ), ENT_QUOTES, config_item( 'charset' ) );
			$data['post_uri'] = trim( $this->input->post( 'post_uri' ) );
			$data['post_feature_image'] = trim( $this->input->post( 'post_feature_image' ) );
				if ( $data['post_feature_image'] == null || !is_numeric( $data['post_feature_image'] ) ) {$data['post_feature_image'] = null;}
			$data['post_comment'] = $this->input->post( 'post_comment' );
				$data['post_comment'] = ( $data['post_comment'] != '1' ? '0' : '1' );
			$data['post_status'] = $this->input->post( 'post_status' );
				if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_publish_unpublish_perm' ) != true ) {$data['post_status'] = '0';}
			$data['meta_title'] = htmlspecialchars( trim( $this->input->post( 'meta_title' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_title'] = ( $data['meta_title'] == null ? null : $data['meta_title'] );
			$data['meta_description'] = htmlspecialchars( trim( $this->input->post( 'meta_description' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_description'] = ( $data['meta_description'] == null ? null : $data['meta_description'] );
			$data['meta_keywords'] = htmlspecialchars( trim( $this->input->post( 'meta_keywords' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_keywords'] = ( $data['meta_keywords'] == null ? null : $data['meta_keywords'] );
			// content settings
			if ( $this->input->post( 'content_show_title' ) == null && $this->input->post( 'content_show_time' ) == null && $this->input->post( 'content_show_author' ) == null ) {
				$data['content_settings'] = null;
			} else {
				$setting['content_show_title'] = $this->input->post( 'content_show_title' );
				$setting['content_show_time'] = $this->input->post( 'content_show_time' );
				$setting['content_show_author'] = $this->input->post( 'content_show_author' );
				$data['content_settings'] = serialize( $setting );
				unset( $setting );
			}
			// revision table
			$data['header_value'] = trim( $this->input->post( 'header_value' ) );
				$data['header_value'] = ( $data['header_value'] == null ? null : $data['header_value'] );
			$data['body_value'] = trim( $this->input->post( 'body_value' ) );
			$data['body_summary'] = trim( $this->input->post( 'body_summary' ) );
				$data['body_summary'] = ( $data['body_summary'] == null ? null : $data['body_summary'] );
			$data['new_revision'] = $this->input->post( 'new_revision' );
			$data['log'] = htmlspecialchars( trim( $this->input->post( 'revision_log' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['log'] = ( $data['log'] == null || $data['new_revision'] != '1' ? null : $data['log'] );
			// load form validation
			$this->load->library( 'form_validation' );
			$this->form_validation->set_rules( 'post_name', 'lang:post_article_name', 'trim|required' );
			$this->form_validation->set_rules( 'body_value', 'lang:post_content', 'trim|required' );
			if ( $this->form_validation->run() == false ) {
				$output['form_status'] = validation_errors( '<div class="txt_error">', '</div>' );
			} else {
				// save result
				$result = $this->posts_model->add( $data );
				if ( $result === true ) {
					$this->load->library( 'session' );
					$this->session->set_flashdata( 'form_status', '<div class="txt_success">' . $this->lang->line( 'admin_saved' ) . '</div>' );
					redirect( 'site-admin/article' );
				} else {
					$output['form_status'] = '<div class="txt_error">' . $result . '</div>';
				}
			}
			// re-populate form
			$output['tid'] = $data['tid'];
			$output['tagid'] = $data['tagid'];
			$output['theme_system_name'] = $data['theme_system_name'];
			$output['post_name'] = $data['post_name'];
			$output['post_uri'] = $data['post_uri'];
			$output['post_feature_image'] = $data['post_feature_image'];
			$output['post_comment'] = $data['post_comment'];
			$output['post_status'] = $data['post_status'];
			$output['meta_title'] = $data['meta_title'];
			$output['meta_description'] = $data['meta_description'];
			$output['meta_keywords'] = $data['meta_keywords'];
			// content settings
			$output['content_show_title'] = ( $this->input->post( 'content_show_title' ) != '1' && $this->input->post( 'content_show_title' ) != '0' ? null : $this->input->post( 'content_show_title' ) );
			$output['content_show_time'] = ( $this->input->post( 'content_show_time' ) != '1' && $this->input->post( 'content_show_time' ) != '0' ? null : $this->input->post( 'content_show_time' ) );
			$output['content_show_author'] = ( $this->input->post( 'content_show_author' ) != '1' && $this->input->post( 'content_show_author' ) != '0' ? null : $this->input->post( 'content_show_author' ) );
			// revision values
			$output['header_value'] = htmlspecialchars( $data['header_value'], ENT_QUOTES, config_item( 'charset' ) );
			$output['body_value'] = htmlspecialchars( $data['body_value'], ENT_QUOTES, config_item( 'charset' ) );
			$output['body_summary'] = htmlspecialchars( $data['body_summary'], ENT_QUOTES, config_item( 'charset' ) );
			$output['new_revision'] = $data['new_revision'];
			$output['revision_log'] = $data['log'];
		}
		// head tags output ##############################
		$output['page_title'] = $this->html_model->gen_title( $this->lang->line( 'post_articles' ) );
		// meta tags
		// link tags
		// script tags
		$script_tags[] = '<script src="'.$this->base_url.'public/js/jquery.textarea.js"></script>';
		$output['page_script'] = $this->html_model->gen_tags( $script_tags );
		unset( $script_tags );
		// end head tags output ##############################
		// output
		$this->generate_page( 'site-admin/article/article_ae_view', $output );
	}// add
	
	
	function ajax_nameuri() {
		if ( $this->input->post() && $this->input->is_ajax_request() ) {
			$post_name = trim( $this->input->post( 'post_name' ) );
			$nodupedit = trim( $this->input->post( 'nodupedit' ) );
			$nodupedit = ( $nodupedit == 'true' ? true : false );
			$id = intval( $this->input->post( 'id' ) );
			$output['post_uri'] = $this->posts_model->nodup_uri( $post_name, $nodupedit, $id );
			// output
			$this->output->set_content_type( 'application/json' );
			$this->output->set_output( json_encode( $output ) );
		}
	}// ajax_nameuri
	
	
	function ajax_searchtag() {
		$_GET['q'] = trim( $this->input->get( 'term' ) );
		$this->taxonomy_model->tax_type = 'tag';
		$list_tags = $this->taxonomy_model->list_tags( 'admin' );
		$output = '';
		if ( isset( $list_tags['items'] ) && is_array( $list_tags['items'] ) ) {
			$i = 0;// important. can't use other number in array key. because jqueryui autocomplete count from 0 and +1 for each array
			foreach ( $list_tags['items'] as $row ) {
				$output[$i]['id'] = $row->tid;
				$output[$i]['value'] = $row->t_name;
				$i++;
			}
		}
		// clear unused items
		unset( $list_tags, $i, $row );
		// output
		$this->output->set_content_type( 'application/json' );
		$this->output->set_output( json_encode( $output ) );
	}// ajax_searchtag
	
	
	function del_rev( $post_id = '', $revision_id = '' ) {
		// check permission
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_delete_revision' ) != true ) {redirect( 'site-admin' );}
		if ( !is_numeric( $post_id ) || !is_numeric( $revision_id ) ) {redirect( 'site-admin/article' );}
		if ( !$this->input->post() ) {
			// head tags output ##############################
			$output['page_title'] = $this->html_model->gen_title( $this->lang->line( 'post_articles' ) );
			// meta tags
			// link tags
			// script tags
			// end head tags output ##############################
			// output
			$this->generate_page( 'site-admin/post/del_rev_view', $output );
		} else {
			// check if revision_id match post_id in revision table and not current
			$this->db->join( 'posts', 'posts.post_id = post_revision.post_id', 'left' );
			$this->db->where( 'post_revision.post_id', $post_id )->where( 'post_revision.revision_id', $revision_id );
			$this->db->where( 'posts.revision_id !=', $revision_id );
			$query = $this->db->get( 'post_revision' );
			if ( $query->num_rows() <= 0 ) {
				$query->free_result();
				unset( $query );
				redirect( 'site-admin/article/edit/'.$post_id );
			}
			$query->free_result();
			// delete revision
			$this->db->where( 'post_id', $post_id )->where( 'revision_id', $revision_id );
			$this->db->delete( 'post_revision' );
			// go back
			redirect( 'site-admin/article/edit/'.$post_id );
		}
	}// del_rev
	
	
	function edit( $post_id = '' ) {
		// check permission (both canNOT edit own and edit other => get out)
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_edit_own_perm' ) != true && $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_edit_other_perm' ) != true ) {redirect( 'site-admin' );}
		// get account id
		$ca_account = $this->account_model->get_account_cookie( 'admin' );
		$my_account_id = $ca_account['id'];
		unset( $ca_account );
		// open posts table for check permission and edit.
		$this->db->join( 'taxonomy_index', 'posts.post_id = taxonomy_index.post_id', 'left outer' );
		$this->db->join( 'post_fields', 'posts.post_id = post_fields.post_id', 'left outer' );
		$this->db->join( 'accounts', 'posts.account_id = accounts.account_id', 'left' );
		$this->db->join( 'post_revision', 'posts.revision_id = post_revision.revision_id', 'inner' );
		$this->db->where( 'post_type', $this->posts_model->post_type );
		$this->db->where( 'language', $this->posts_model->language );
		$this->db->where( 'posts.post_id', $post_id );
		$this->db->group_by( 'posts.post_id' );
		$query = $this->db->get( 'posts' );
		if ( $query->num_rows() <= 0 ) {$query->free_result(); redirect( 'site-admin/article' );}// not found
		$row = $query->row();
		// check permissions-----------------------------------------------------------
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_edit_own_perm' ) && $row->account_id != $my_account_id ) {
			// this user has permission to edit own post, but NOT editing own post
			if ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_edit_other_perm' ) ) {
				// this user has NOT permission to edit other's post, but editing other's post
				$query->free_result();
				unset( $row, $query, $my_account_id );
				redirect( 'site-admin' );
			}
		} elseif ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_edit_own_perm' ) && $row->account_id == $my_account_id ) {
			// this user has NOT permission to edit own post, but editing own post.
			$query->free_result();
			unset( $row, $query, $my_account_id );
			redirect( 'site-admin' );
		}
		// end check permissions-----------------------------------------------------------
		// list themes for select
		$output['list_theme'] = $this->themes_model->list_enabled_themes();
		// list categories for check
		$this->taxonomy_model->tax_type = 'category';
		$output['list_category'] = $this->taxonomy_model->list_item();
		// preset settings and values---------------------------------------------------------
		$output['post_id'] = $post_id;
		$output['post_comment'] = '1';
		$output['post_status'] = '1';
		// load settings and values from db for edit.---------------------------------------
		$this->taxonomy_model->tax_type = 'tag';
		$ttlist = $this->taxonomy_model->list_taxterm_index( $post_id );
		$tid = array();
		if ( !empty( $ttlist ) ) {
			foreach ( $ttlist as $atid ) {
				$tid[] = $atid->tid;
			}
		}
		unset( $ttlist );
		$output['tagid'] = $tid;
		//
		$this->taxonomy_model->tax_type = 'category';
		$ttlist = $this->taxonomy_model->list_taxterm_index( $post_id );
		$tid = array();
		if ( !empty( $ttlist ) ) {
			foreach ( $ttlist as $atid ) {
				$tid[] = $atid->tid;
			}
		}
		unset( $ttlist );
		$output['tid'] = $tid;
		//
		$output['theme_system_name'] = $row->theme_system_name;
		$output['post_name'] = $row->post_name;
		$output['post_uri'] = $row->post_uri;
		$output['post_feature_image'] = $row->post_feature_image;
		$output['post_comment'] = $row->post_comment;
		$output['post_status'] = $row->post_status;
		$output['meta_title'] = $row->meta_title;
		$output['meta_description'] = $row->meta_description;
		$output['meta_keywords'] = $row->meta_keywords;
			// content settings
			$content_settings = unserialize( $row->content_settings );
		$output['content_show_title'] = $content_settings['content_show_title'];
		$output['content_show_time'] = $content_settings['content_show_time'];
		$output['content_show_author'] = $content_settings['content_show_author'];
			// revision table
		$output['revision_id'] = $row->revision_id;
		$output['header_value'] = htmlspecialchars( $row->header_value, ENT_QUOTES, config_item( 'charset' ) );
		$output['body_value'] = htmlspecialchars( $row->body_value, ENT_QUOTES, config_item( 'charset' ) );
		$output['body_summary'] = htmlspecialchars( $row->body_summary, ENT_QUOTES, config_item( 'charset' ) );
			// send row for other use.
		$output['row'] = $row;
		// list revision
		$this->db->join( 'accounts', 'post_revision.account_id = accounts.account_id', 'left' );
		$this->db->where( 'post_id', $post_id );
		$this->db->order_by( 'revision_date', 'desc' );
		$query2 = $this->db->get( 'post_revision' );
		$output['count_revision'] = $query2->num_rows();
		$output['list_revision'] = $query2->result();
		$query2->free_result();
		// save action
		if ( $this->input->post() ) {
			$data['tid'] = $this->input->post( 'tid' );// categories
			$data['tagid'] = $this->input->post( 'tagid' );// tags
			$data['post_id'] = $post_id;
			$data['theme_system_name'] = trim( $this->input->post( 'theme_system_name' ) );
				$data['theme_system_name'] = ( $data['theme_system_name'] == null ? null : $data['theme_system_name'] );
			$data['post_name'] = htmlspecialchars( trim( $this->input->post( 'post_name' ) ), ENT_QUOTES, config_item( 'charset' ) );
			$data['post_uri'] = trim( $this->input->post( 'post_uri' ) );
			$data['post_feature_image'] = trim( $this->input->post( 'post_feature_image' ) );
				if ( $data['post_feature_image'] == null || !is_numeric( $data['post_feature_image'] ) ) {$data['post_feature_image'] = null;}
			$data['post_comment'] = $this->input->post( 'post_comment' );
				$data['post_comment'] = ( $data['post_comment'] != '1' ? '0' : '1' );
			if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_publish_unpublish_perm' ) ) {
				$data['post_status'] = $this->input->post( 'post_status' );
				$data['post_status'] = ( $data['post_status'] == '1' ? '1' : '0' );
			}
			$data['meta_title'] = htmlspecialchars( trim( $this->input->post( 'meta_title' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_title'] = ( $data['meta_title'] == null ? null : $data['meta_title'] );
			$data['meta_description'] = htmlspecialchars( trim( $this->input->post( 'meta_description' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_description'] = ( $data['meta_description'] == null ? null : $data['meta_description'] );
			$data['meta_keywords'] = htmlspecialchars( trim( $this->input->post( 'meta_keywords' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['meta_keywords'] = ( $data['meta_keywords'] == null ? null : $data['meta_keywords'] );
			// content settings
			if ( $this->input->post( 'content_show_title' ) == null && $this->input->post( 'content_show_time' ) == null && $this->input->post( 'content_show_author' ) == null ) {
				$data['content_settings'] = null;
			} else {
				$setting['content_show_title'] = $this->input->post( 'content_show_title' );
				$setting['content_show_time'] = $this->input->post( 'content_show_time' );
				$setting['content_show_author'] = $this->input->post( 'content_show_author' );
				$data['content_settings'] = serialize( $setting );
				unset( $setting );
			}
			// revision table
			$data['header_value'] = trim( $this->input->post( 'header_value' ) );
				$data['header_value'] = ( $data['header_value'] == null ? null : $data['header_value'] );
			$data['body_value'] = trim( $this->input->post( 'body_value' ) );
			$data['body_summary'] = trim( $this->input->post( 'body_summary' ) );
				$data['body_summary'] = ( $data['body_summary'] == null ? null : $data['body_summary'] );
			$data['new_revision'] = $this->input->post( 'new_revision' );
			$data['log'] = htmlspecialchars( trim( $this->input->post( 'revision_log' ) ), ENT_QUOTES, config_item( 'charset' ) );
				$data['log'] = ( $data['log'] == null || $data['new_revision'] != '1' ? null : $data['log'] );
			// load form validation
			$this->load->library( 'form_validation' );
			$this->form_validation->set_rules( 'post_name', 'lang:post_article_name', 'trim|required' );
			$this->form_validation->set_rules( 'body_value', 'lang:post_content', 'trim|required' );
			if ( $this->form_validation->run() == false ) {
				$output['form_status'] = validation_errors( '<div class="txt_error">', '</div>' );
			} else {
				// save result
				$result = $this->posts_model->edit( $data );
				if ( $result === true ) {
					$this->load->library( 'session' );
					$this->session->set_flashdata( 'form_status', '<div class="txt_success">' . $this->lang->line( 'admin_saved' ) . '</div>' );
					redirect( 'site-admin/article' );
				} else {
					$output['form_status'] = '<div class="txt_error">' . $result . '</div>';
				}
			}
			// re-populate form
			$output['tid'] = $data['tid'];
			$output['tagid'] = $data['tagid'];
			$output['theme_system_name'] = $data['theme_system_name'];
			$output['post_name'] = $data['post_name'];
			$output['post_uri'] = $data['post_uri'];
			$output['post_feature_image'] = $data['post_feature_image'];
			$output['post_comment'] = $data['post_comment'];
			if ( isset( $data['post_status'] ) ) {
				$output['post_status'] = $data['post_status'];
			}
			$output['meta_title'] = $data['meta_title'];
			$output['meta_description'] = $data['meta_description'];
			$output['meta_keywords'] = $data['meta_keywords'];
			// content settings
			$output['content_show_title'] = ( $this->input->post( 'content_show_title' ) != '1' && $this->input->post( 'content_show_title' ) != '0' ? null : $this->input->post( 'content_show_title' ) );
			$output['content_show_time'] = ( $this->input->post( 'content_show_time' ) != '1' && $this->input->post( 'content_show_time' ) != '0' ? null : $this->input->post( 'content_show_time' ) );
			$output['content_show_author'] = ( $this->input->post( 'content_show_author' ) != '1' && $this->input->post( 'content_show_author' ) != '0' ? null : $this->input->post( 'content_show_author' ) );
			// revision values
			$output['header_value'] = htmlspecialchars( $data['header_value'], ENT_QUOTES, config_item( 'charset' ) );
			$output['body_value'] = htmlspecialchars( $data['body_value'], ENT_QUOTES, config_item( 'charset' ) );
			$output['body_summary'] = htmlspecialchars( $data['body_summary'], ENT_QUOTES, config_item( 'charset' ) );
			$output['new_revision'] = $data['new_revision'];
			$output['revision_log'] = $data['log'];
		}
		// head tags output ##############################
		$output['page_title'] = $this->html_model->gen_title( $this->lang->line( 'post_articles' ) );
		// meta tags
		// link tags
		// script tags
		$script_tags[] = '<script src="'.$this->base_url.'public/js/jquery.textarea.js"></script>';
		$output['page_script'] = $this->html_model->gen_tags( $script_tags );
		unset( $script_tags );
		// end head tags output ##############################
		// output
		$this->generate_page( 'site-admin/article/article_ae_view', $output );
	}// edit
	
	
	function index() {
		// check permission
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_viewall_perm' ) != true ) {redirect( 'site-admin' );}
		// list category for select filter
		$this->taxonomy_model->tax_type = 'category';
		$output['list_category'] = $this->taxonomy_model->list_item();
		// sort, orders, search, tid
		$output['orders'] = strip_tags( trim( $this->input->get( 'orders' ) ) );
		$output['sort'] = ($this->input->get( 'sort' ) == null || $this->input->get( 'sort' ) == 'desc' ? 'asc' : 'desc' );
		$output['q'] = htmlspecialchars( trim( $this->input->get( 'q' ) ) );
		$output['tid'] = strip_tags( trim( $this->input->get( 'tid' ) ) );
		// load session for flashdata
		$this->load->library( 'session' );
		$form_status = $this->session->flashdata( 'form_status' );
		if ( $form_status != null ) {
			$output['form_status'] = $form_status;
		}
		unset( $form_status );
		// list item
		$output['list_item'] = $this->posts_model->list_item( 'admin' );
		if ( is_array( $output['list_item'] ) ) {
			$output['pagination'] = $this->pagination->create_links();
		}
		// my account id
		$ca_account = $this->account_model->get_account_cookie( 'admin' );
		$output['my_account_id'] = $ca_account['id'];
		unset( $ca_account );
		// head tags output ##############################
		$output['page_title'] = $this->html_model->gen_title( $this->lang->line( 'post_articles' ) );
		// meta tags
		// link tags
		// script tags
		// end head tags output ##############################
		// output
		$this->generate_page( 'site-admin/article/article_view', $output );
	}// index
	
	
	function process_bulk() {
		// get account id
		$ca_account = $this->account_model->get_account_cookie( 'admin' );
		$my_account_id = $ca_account['id'];
		unset( $ca_account );
		//
		$id = $this->input->post( 'id' );
		if ( !is_array( $id ) ) {redirect( 'site-admin/article' );}
		$act = trim( $this->input->post( 'act' ) );
		if ( $act == 'publish' ) {
			// check permission
			if ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_publish_unpublish_perm' ) ) {redirect( 'site-admin/article' );}
			foreach ( $id as $an_id ) {
				// open for check
				$this->db->where( 'post_id', $an_id );
				$query = $this->db->get( 'posts' );
				if ( $query->num_rows() <= 0 ) {$query->free_result(); continue;}
				$row = $query->row();
				$query->free_result();
				// update
				$this->db->where( 'post_id', $an_id );
				$this->db->set( 'post_status', '1' );
				$this->db->set( 'post_update', time() );
				$this->db->set( 'post_update_gmt', local_to_gmt( time() ) );
				if ( $row->post_publish_date == null && $row->post_publish_date_gmt == null ) {
					$this->db->set( 'post_publish_date', time() );
					$this->db->set( 'post_publish_date_gmt', local_to_gmt( time() ) );
					// publish plugin
					$this->modules_plug->do_action( 'post_published_byid', $an_id );
				}
				$this->db->update( 'posts' );
			}
		} elseif( $act == 'unpublish' ) {
			// check permission
			if ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_publish_unpublish_perm' ) ) {redirect( 'site-admin/article' );}
			foreach ( $id as $an_id ) {
				$this->db->where( 'post_id', $an_id );
				$this->db->set( 'post_status', '0' );
				$this->db->set( 'post_update', time() );
				$this->db->set( 'post_update_gmt', local_to_gmt( time() ) );
				$this->db->update( 'posts' );
			}
		} elseif ( $act == 'del' ) {
			// check both permission
			if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_delete_own_perm' ) != true && $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_delete_other_perm' ) != true ) {redirect( 'site-admin/article' );}
			foreach ( $id as $an_id ) {
				$this->db->where( 'post_id', $an_id );
				$query = $this->db->get( 'posts' );
				if ( $query->num_rows() <= 0 ) {$query->free_result(); continue;}
				$row = $query->row();
				$query->free_result();
				// check permissions-----------------------------------------------------------
				if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_article_delete_own_perm' ) && $row->account_id != $my_account_id ) {
					// this user has permission to delete own post, but NOT delete own post
					if ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_delete_other_perm' ) ) {
						// this user has NOT permission to delete other's post, but deleting other's post
						$query->free_result();
						unset( $row, $query );
						continue;
					}
				} elseif ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_delete_own_perm' ) && $row->account_id == $my_account_id ) {
					// this user has NOT permission to delete own post, but deleting own post.
					$query->free_result();
					unset( $row, $query );
					continue;
				}
				// end check permissions-----------------------------------------------------------
				$this->posts_model->delete( $an_id );
			}
			// update total posts in taxonomy term
			$query = $this->db->get( 'taxonomy_term_data' );
			foreach ( $query->result() as $row ) {
				$this->taxonomy_model->update_total_post( $row->tid );
			}
			$query->free_result();
			unset( $query, $row );
		}
		// go back
		$this->load->library( 'user_agent' );
		if ( $this->agent->is_referral() ) {
			redirect( $this->agent->referrer() );
		} else {
			redirect( 'site-admin/article' );
		}
	}// process_bulk
	
	
	function reorder( $post_id = '', $tid = '', $move = '' ) {
		// check permission
		if ( !$this->account_model->check_admin_permission( 'post_article_perm', 'post_article_sort_perm' ) ) {redirect( 'site-admin/article' );}
		//
		if ( !is_numeric( $post_id ) || !is_numeric( $tid ) || ($move != 'up' && $move != 'dn' ) ) {redirect( 'site-admin/article' );}
		$this->load->library( 'user_agent' );
		// get current position
		$this->db->where( 'post_id', $post_id )->where( 'tid', $tid );
		$query = $this->db->get( 'taxonomy_index' );
		if ( $query->num_rows() <= 0 ) {$query->free_result(); redirect( 'site-admin/article' );}// not found
		$row = $query->row();
		$query->free_result();
		if ( $move == 'up' ) {
			// check if there is higher position
			$this->db->where( 'tid', $tid )->where( 'position >', $row->position );
			$this->db->order_by( 'position', 'asc' );
			$query2 = $this->db->get( 'taxonomy_index' );
			if ( $query2->num_rows() <= 0 ) {$query2->free_result(); redirect( $this->agent->referrer() );}// not found. this is heighest position, no more up.
			$row2 = $query2->row();
			$query2->free_result();
			// update heigher to -1
			if ( $row2->position-$row->position > 1 ) {
				$position = $row->position;
			} else {
				$position = $row2->position-1;
			}
			$this->db->set( 'position', $position );
			$this->db->where( 'index_id', $row2->index_id );
			$this->db->update( 'taxonomy_index' );
			// update current position to heigher
			$this->db->set( 'position', $row->position+1 );
			$this->db->where( 'index_id', $row->index_id );
			$this->db->update( 'taxonomy_index' );
		} elseif ( $move == 'dn' ) {
			// check if there is lower position
			$this->db->where( 'tid', $tid )->where( 'position <', $row->position );
			$this->db->order_by( 'position', 'desc' );
			$query2 = $this->db->get( 'taxonomy_index' );
			if ( $query2->num_rows() <= 0 ) {$query2->free_result(); redirect( $this->agent->referrer() );}// not found. this is heighest position, no more up.
			$row2 = $query2->row();
			$query2->free_result();
			// update lower to +1
			$this->db->set( 'position', $row2->position+1 );
			$this->db->where( 'index_id', $row2->index_id );
			$this->db->update( 'taxonomy_index' );
			// update current position to lower
			if ( $row->position-$row2->position > 1 ) {
				$position = $row2->position;
			} else {
				$position = $row->position-1;
			}
			$this->db->set( 'position', $position );
			$this->db->where( 'index_id', $row->index_id );
			$this->db->update( 'taxonomy_index' );
		}
		unset( $position, $query, $query2, $row, $row2 );
		redirect( $this->agent->referrer() );
	}// reorder
	
	
	function revert( $post_id = '', $revision_id = '' ) {
		// check permission
		if ( $this->account_model->check_admin_permission( 'post_article_perm', 'post_revert_revision' ) != true ) {redirect( 'site-admin' );}
		if ( !is_numeric( $post_id ) || !is_numeric( $revision_id ) ) {redirect( 'site-admin/article' );}
		if ( !$this->input->post() ) {
			// head tags output ##############################
			$output['page_title'] = $this->html_model->gen_title( $this->lang->line( 'post_articles' ) );
			// meta tags
			// link tags
			// script tags
			// end head tags output ##############################
			// output
			$this->generate_page( 'site-admin/post/revert_view', $output );
		} else {
			// check if revision_id match post_id in revision table
			$this->db->where( 'post_id', $post_id )->where( 'revision_id', $revision_id );
			$query = $this->db->get( 'post_revision' );
			if ( $query->num_rows() <= 0 ) {
				$query->free_result();
				unset( $query );
				redirect( 'site-admin/article/edit/'.$post_id );
			}
			$query->free_result();
			// update revision id to posts table
			$this->db->set( 'revision_id', $revision_id );
			$this->db->where( 'post_id', $post_id );
			$this->db->update( 'posts' );
			// go back
			redirect( 'site-admin/article/edit/'.$post_id );
		}
	}// revert
	

}
