<?php
function hba_theme_alpha_preprocess_node(&$vars) {
  
  //  custom meta tags for different node types!!!
  
  if(isset($vars['type']) && $vars['type'] == 'species') {

    $page_keywords_title = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'property' => 'og:title',
                      'content' => $vars['title'],
                        )
                  );
    $page_keywords_type = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'property' => 'og:type',
                      'content' => 'website',
                        )
                  );
    $page_keywords_url = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'property' => 'og:url',
                      'content' => 'http://'.$_SERVER['HTTP_HOST'] . request_uri(),
                        )
                  );
                  
    $url_jpg = db_query("SELECT file.uri FROM field_data_field_figure_sp fig, field_data_field_figure_img_jpg jpg, field_data_field_figure_order_sp ord, file_managed file
    where fig.field_figure_sp_nid = :current_node_id
    and fig.entity_id=jpg.entity_id
    and file.fid=jpg.field_figure_img_jpg_fid
    and ord.entity_id=fig.entity_id
    and ord.field_figure_order_sp_value=0
    limit 0,1", array(':current_node_id' => $vars['nid']))->fetchField();
    $url_image = 'http://www.hbw.com/sites/default/files/styles/large_a/'.str_replace('://','/',$url_jpg);
    $page_keywords_image = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'property' => 'og:image',
                      'content' => $url_image,
                        )
                  );

   drupal_add_html_head($page_keywords_title, 'page_keywords_title');
   drupal_add_html_head($page_keywords_type, 'page_keywords_type');
   drupal_add_html_head($page_keywords_url, 'page_keywords_url');
   drupal_add_html_head($page_keywords_image, 'page_keywords_image');


// podriem fer un node load, però tampoc obtenim tot el que volem aixi que potser no surt a compte...
//   $current_species_node = node_load($vars['nid']);
//dpm($current_species_node);
// hauriem de fer un node load wrapper o com es digui, per tenir els noms dels autors en canvi d'un llistat d'nids, etc...

    //meta tag description
    $sp_descr_notes = substr(substr($vars['title'],0,strpos($vars['title'],'(')) . strip_tags(db_query("SELECT field_sp_descr_notes_value FROM field_data_field_sp_descr_notes f where entity_id = :current_node_id limit 0,1", array(':current_node_id' => $vars['nid']))->fetchField()),0,200);
    $sp_descr_notes = str_replace('&amp;','&', $sp_descr_notes);
    $sp_descr_notes = str_replace('&ndash;','-', $sp_descr_notes);
    
    $page_keywords_description = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'name' => 'description',
                      'content' => $sp_descr_notes,
                        )
                  );
    $page_keywords_og_description = array(
                     '#type' => 'html_tag',
                     '#tag' => 'meta',
                     '#attributes' => array(
                      'name' => 'og:description',
                      'content' => $sp_descr_notes,
                        )
                  );
                  
   drupal_add_html_head($page_keywords_description, 'page_keywords_description');
   drupal_add_html_head($page_keywords_og_description, 'page_keywords_og_description');

  } // species nodes
  
  if(isset($vars['type']) && $vars['type'] == 'family') {
    if(isset($vars['field_fam_summary']['und'])) {
      $fam_summary = $vars['title'] . htmlspecialchars(strip_tags($vars['field_fam_summary']['und'][0]['value']));
      $page_keywords_description = array(
                 '#type' => 'html_tag',
                 '#tag' => 'meta',
                 '#attributes' => array(
                  'name' => 'description',
                  'content' => $fam_summary,
                    )
              );
      $page_keywords_og_description = array(
                 '#type' => 'html_tag',
                 '#tag' => 'meta',
                 '#attributes' => array(
                  'name' => 'og:description',
                  'content' => $fam_summary,
                    )
              );
      $page_keywords_og_title = array(
                 '#type' => 'html_tag',
                 '#tag' => 'meta',
                 '#attributes' => array(
                  'name' => 'og:title',
                  'content' => $vars['title'],
                    )
              );
      drupal_add_html_head($page_keywords_description, 'page_keywords_description');
      drupal_add_html_head($page_keywords_og_description, 'page_keywords_og_description');
      drupal_add_html_head($page_keywords_og_title, 'page_keywords_og_title');
    } // isset
  
  }// family

  if(isset($vars['type']) && $vars['type'] == 'order') {
    $families_sql = db_query("SELECT fam_eng.field_fam_eng_value as eng FROM field_data_field_fam_eng fam_eng, field_data_field_fam_order fam_ord where fam_ord.entity_id=fam_eng.entity_id and fam_ord.field_fam_order_nid=:current_node_id limit 0,9", array(':current_node_id' => $vars['nid']));
    $families_names = '';
    foreach ($families_sql as $record) {
      $families_names .= $record->eng .', ';
    }
    
    $order_summary = 'Order '. $vars['title'] . ' contains bird families like '.$families_names;
    $page_keywords_description = array(
           '#type' => 'html_tag',
           '#tag' => 'meta',
           '#attributes' => array(
            'name' => 'description',
            'content' => $order_summary,
              )
        );
    drupal_add_html_head($page_keywords_description, 'page_keywords_description');
  } // order
  
}
