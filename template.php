<?php

/**
 * @file
 * This file is empty by default because the base theme chain (Alpha & Omega) provides
 * all the basic functionality. However, in case you wish to customize the output that Drupal
 * generates through Alpha & Omega this file is a good place to do so.
 * 
 * Alpha comes with a neat solution for keeping this file as clean as possible while the code
 * for your subtheme grows. Please read the README.txt in the /preprocess and /process subfolders
 * for more information on this topic.
 */

/*
function hba_theme_menu_link__menu_sections(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }

  $element['#attributes']['class'][] = 'clearfix';
  
  $output = l($element['#title'], $element['#href']);
  $description = '<p class="desc">' . $element['#localized_options']['attributes']['title'];   
  $element['#attributes']['class'][] = 'menu_' . strtolower(transliteration_clean_filename($element['#title']));
  return '<li' . drupal_attributes($element['#attributes']) . '><div class="wrapper clearfix"><span class="menu_image"></span>' . $output . $description . $sub_menu . "</div></li>\n";
}
*/

// Crear un template per node add/edit
/*function hba_theme_theme() {
  return array(
    'my_record_node_form' => array(
      'arguments' => array(
          'form' => NULL,
      ),
      'template' => 'templates/my-record-node-form', // set the path here if not in root theme directory
      'render element' => 'form',
    ),
  );
}*/ // DO THE SAME FOR SEVEN THEME!!!

/**
 * Create a template for Birdlist node form
 */
// http://www.wdtutorials.com/2013/01/22/drupal-7-how-override-forms-using-custom-template
function hba_theme_theme() {
  return array(
    'locality_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/locality-node-form',
      'render element' => 'form'
    ),
    'video_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/video-node-form',
      'render element' => 'form'
    ),
    'picture_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/picture-node-form',
      'render element' => 'form'
    ),
    'audio_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/audio-node-form',
      'render element' => 'form'
    ),
  );
}

function hba_theme_form_alter(&$form, &$form_state, $form_id) {
  switch($form_id) {
    case 'user_login_block':
      $form['name']['#attributes']['placeholder'] = t('Username');
      $form['pass']['#attributes']['placeholder'] = t('Password...');
      break;
    
    case 'views_exposed_form':
      // View Imported data
      if($form_state['view']->name == 'import_overview') {
        $form['date_op']['#default_value'] = '>=';
        $form['date_op']['#options']['<='] = 'is later to';
        $form['date_op']['#options']['>='] = 'is earlier to';
        unset($form['date_op']['#options']['=']);
        unset($form['date_op']['#options']['!=']);
        unset($form['date_op']['#options']['<']);
        unset($form['date_op']['#options']['>']);
        unset($form['date_op']['#options']['between']);
        unset($form['date_op']['#options']['not between']);
        unset($form['date_op']['#options']['empty']);
        unset($form['date_op']['#options']['not empty']);
        unset($form['date_op']['#options']['regular_expression']);
      }

      // Sightings: display data
      if($form_state['view']->name == 'sightings' || $form_state['view']->name == 'sightings_bulk_change_species'){
        $form['date']['min']['#title'] = 'Start date';
        $form['date']['max']['#title'] = 'End date';
        unset($form['dt_m']['#options']['All']);
        $form['dt_m']['#default_value'] = '';
        unset($form['dt_y']['#options']['All']);
        $form['dt_y']['#default_value'] = '';

        // Hide Territory filter description
        $form['tid']['#description'] = '';

        // Hide filters that are cloned after
        //$form['figure']['#access'] = FALSE;
        //unset($form['figure'];

        // https://www.drupal.org/node/1379492
        // Start date + End date
        // Si les 2 dates estan omplides, hem de transformar el format de les dates per a que sigui ordenable
        if(!empty($form_state['input']['date']['min']) && !empty($form_state['input']['date']['max'])) {
          //execute manage_custom_filters function submit in first step on $form['#submit'] array
          array_unshift($form['#submit'], 'hba_theme_form_submit');
        }

        // Ringed
        $form['ringed_op']['#options']['>='] = '>=';
        $form['ringed_op']['#options']['='] = '=';
        unset($form['ringed_op']['#options']['>']);
        unset($form['ringed_op']['#options']['<']);
        unset($form['ringed_op']['#options']['<=']);
        unset($form['ringed_op']['#options']['!=']);
        unset($form['ringed_op']['#options']['between']);
        unset($form['ringed_op']['#options']['not between']);
        unset($form['ringed_op']['#options']['empty']);
        unset($form['ringed_op']['#options']['not empty']);
        unset($form['ringed_op']['#options']['regular_expression']);
      }

      // My Trips / My Birdlists
      if(($form_state['view']->name == 'my_record' && $form_state['view']->current_display == 'page') || $form_state['view']->name == 'mytrips') {
        unset($form['dt_m']['#options']['All']);
        $form['dt_m']['#default_value'] = '';
        unset($form['dt_y']['#options']['All']);
        $form['dt_y']['#default_value'] = '';
        $form['start_date']['min']['#title'] = 'Start date between...';
        $form['start_date']['max']['#title'] = '...and';
      }

      // Sightings: display list
      if($form_state['view']->name == 'myb_list') {
        // "Country status" filter doesn't work if no country o more than 1 country is selected in the Global Filter form
        //if(empty($_SESSION['global_filter']['view_taxo_paisos']) || !empty($_SESSION['global_filter']['view_taxo_paisos'][1]) || !global_filter_has_no_continent()) {
        //if(empty($_SESSION['global_filter']['view_taxo_paisos']) || !empty($_SESSION['global_filter']['view_taxo_paisos'][1])) {
        if(empty($_SESSION['global_filter']['view_taxo_paisos'])) {
          unset($form['country_status'],
                $form['#info']['filter-country_status'],
                $form_state['view']->display_handler->options['filters']['country_status'],
                $form_state['view']->display_handler->handlers['filter']['country_status'],
                $form_state['view']->filter['country_status']);
        }
      } // if name = myb_list

      // My birding species maintenance
      if($form_state['view']->name == 'mybirding_sp_maintenance') {
        // escaped as filter: from select to checkbox
        /*$form['esc']['#type'] = 'checkbox';
        $form['esc']['#options']['All'] = '- Any -';
        $form['esc']['#options']['1'] = 'Yes';
        $form['esc']['#options']['0'] = 'No';
        $form['esc']['#title'] = 'Escaped';
        $form['esc']['#default_value'] = 'All';
        $form['esc']['#return_value'] = 'All';
        $form['esc']['#theme'] = 'checkbox';

        // no count as filter: from select to checkbox
        $form['nocount']['#type'] = 'checkbox';
        $form['nocount']['#options']['All'] = '- Any -';
        $form['nocount']['#options']['1'] = 'Yes';
        $form['nocount']['#options']['0'] = 'No';
        $form['nocount']['#title'] = 'No count';
        $form['nocount']['#default_value'] = 'All';
        $form['nocount']['#return_value'] = 'All';
        $form['nocount']['#theme'] = 'checkbox';*/

        // Select "Has sightings?"
        //dsm($form['sight'], '$form[sight]');
        unset($form['sight']['#options']['in']);
        unset($form['sight']['#options']['out']);
        unset($form['sight']['#options']['both']);
      }

      // Update the territory of many birdlists at once (http://www.hbw.com/mybirding/birdlist/territory)
      if($form_state['view']->name == 'bulk_territory_reference') {
        //if(empty($_SESSION['global_filter']['view_taxo_paisos'])) {
        //$form_state['input']['source2'] == 'All'
        $form['source2'] = array(
          '#type' => 'select',
          '#title' => t('Import source'),
          '#options' => array(
            'All' => '- Any -',
            'BirdBase' => 'BirdBase',
            'Birdtrack' => 'Birdtrack',
            'eBird' => 'eBird',
            'Excel' => 'Excel',
            'Ornitho' => 'Ornitho',
          ),
          '#default_value' => 'All',
        );
        
        $form['source2']['#weight'] = -1000;
        //}
      } // if name = bulk_territory_reference
    break;

    // View Update the territory of many birdlists at once (http://www.dev1.hbw.com/mybirding/birdlist/territory)
    case 'views_form_bulk_territory_reference_page':
      //dsm($form, '$form');
      // Hide title form fieldset with the "Update territory" button
      $form['select']['#title'] = '';
      $form['select']['#weight'] = 1000;
      // Pes de la taula respecte al fieldset amb el botó "Update territory"
      $form['output']['#weight'] = 0;

      // 2n pas del bulk process: camp "Country/territory"
      // Canviar el nom del botó "Next"
      $form['actions']['submit']['#value'] = 'Apply now';
      // Canviar el label del camp "Country/territory"
      $form['bundle_locality']['field_myr_country']['und'][0]['target_id']['#title'] = 'Choose the new territory to be applied to all of the selected birdlists.';
      // I la seva descripció
      $form['bundle_locality']['field_myr_country']['und'][0]['target_id']['#description'] = ''; //Choose a country/territory for the selected birdlist(s).
    break;

    case 'global_filter_1':
      // Treure del GF els mega-territoris sino el servidor es pot penjar al afegir molts sub-territoris a la query
      if(arg(0) == 'printable-checklist') {
        unset($form['view_taxo_paisos']['#options'][4905]); // R ABA Area
        unset($form['view_taxo_paisos']['#options'][4583]); // R Africa
        unset($form['view_taxo_paisos']['#options'][4906]); // R AOU Area-North
        unset($form['view_taxo_paisos']['#options'][4907]); // R AOU Area-South
        unset($form['view_taxo_paisos']['#options'][4584]); // R Asia
        unset($form['view_taxo_paisos']['#options'][4895]); // R Atlantic/Arctic Oceans
        unset($form['view_taxo_paisos']['#options'][4900]); // R Australasia
        unset($form['view_taxo_paisos']['#options'][4902]); // R Central America
        unset($form['view_taxo_paisos']['#options'][4926]); // R Eastern Africa
        unset($form['view_taxo_paisos']['#options'][4912]); // R Eastern Hemisphere
        unset($form['view_taxo_paisos']['#options'][4909]); // R Eurasia
        unset($form['view_taxo_paisos']['#options'][4582]); // R Europe
        unset($form['view_taxo_paisos']['#options'][4894]); // R Indian Ocean
        unset($form['view_taxo_paisos']['#options'][4921]); // R Indian Subcontinent
        unset($form['view_taxo_paisos']['#options'][28292]); // R Indonesia Global
        unset($form['view_taxo_paisos']['#options'][4924]); // R Malagasy Zone
        unset($form['view_taxo_paisos']['#options'][4908]); // R North America
        unset($form['view_taxo_paisos']['#options'][4901]); // R Pacific Ocean
        unset($form['view_taxo_paisos']['#options'][4903]); // R South America
        unset($form['view_taxo_paisos']['#options'][4904]); // R South Polar
        unset($form['view_taxo_paisos']['#options'][4928]); // R South-East Asia
        unset($form['view_taxo_paisos']['#options'][4927]); // R Southern Africa
        unset($form['view_taxo_paisos']['#options'][4923]); // R Sunda Islands
        unset($form['view_taxo_paisos']['#options'][4896]); // R West Indias
        unset($form['view_taxo_paisos']['#options'][4925]); // R Western Africa
        unset($form['view_taxo_paisos']['#options'][4911]); // R Western Hemisphere
        unset($form['view_taxo_paisos']['#options'][4910]); // R Western Palearctic
      }

   //   if((arg(0) == 'mybirding' && arg(1) == 'list') || arg(0) == 'printable-checklist') {
   //     $form['view_taxo_paisos']['#description'] = 'You can choose up to 3 territories.';

        // L'usuari afegeix més d'1 territori al Geographic filter i fa clic en algun botó que inclou filtrar per country_status -> se li mostra una advertencia
        /*if(!empty($form['view_taxo_paisos']['#default_value'][1]) && isset($_GET['country_status']) && $_GET['country_status'] !='All') {
          drupal_set_message('You can\'t filter by species status with more than 1 territory in the Geographic filter.', 'warning', FALSE);
        }*/
  //    }
    //  else {
        $countries = array();
        
        if(!empty($_SESSION['global_filter']['view_taxo_paisos'])) {
          foreach($_SESSION['global_filter']['view_taxo_paisos'] as $country) {
            $continents = bird_taxonomies_continents();
            
            if(isset($continents[$country])) {
              $countries[] = $continents[$country]['name'];
            }
            else {
              $term = taxonomy_term_load($country);
              if(isset($term->name)) {
                $countries[] = $term->name;
              }
              else {
                $countries[] = t('All');
              }
            }
          }

          $countries = implode(', ', $countries);
          $countries = truncate_utf8(ucfirst($countries),38,false,'...');
          $form['filter']['#markup'] = '<h3>' . t('<a href="#" class="change">Filter by: !countries</a>', array('!countries' => $countries)) . '</h3>';
        }
        else {
          $form['filter']['#markup'] = '<h3>No active filters</h3>';
        }
   //   }
      $form['filter']['#weight'] = -10;

      $form['inputs']['view_taxo_paisos'] = $form['view_taxo_paisos'];
      unset($form['view_taxo_paisos']);
      $form['inputs']['submit'] = $form['submit'];
      unset($form['submit']);

      $form['inputs']['#type'] = 'fieldset';
      //}
    break;
  }
}

/**
 * 
 */
function hba_theme_form_submit($form, &$form_state) {
  $var_time_min = date_create_from_format('j M Y - H:i', $_GET['date']['min']);
  $var_time_min_query = date_format($var_time_min,'Y-m-d H:i:s');
  $var_time_max = date_create_from_format('j M Y - H:i', $_GET['date']['max']);
  $var_time_max_query = date_format($var_time_max,'Y-m-d H:i:s');
  //modify form_state values of concerned field
  $form_state['values']['date']['min'] = $var_time_min_query;
  $form_state['values']['date']['max'] = $var_time_max_query;
}

/**
 * Implements theme_filter_tips_more_info().
 *
 * Open filter tips link in new page. Prevents data loss.
 * @see http_://drupal.org/node/87994#comment-4713488
 */
function hba_theme_filter_tips_more_info() {
  return '<p>' . l(t('More information about text formats'), 'filter/tips', array('attributes' => array('target' => '_blank'))) . '</p>';
}
/**
* Remove the comment filters' tips
* http://drupal.org/node/35122#comment-4513554
*/
/*
function hba_filter_tips($tips, $long = FALSE, $extra = '') {
  return '';
}*/
/**
* Remove the comment filter's more information tips link
*/
/*
function hba_filter_tips_more_info () {
  return '';
}*/

function hba_theme_process_page(&$variables) {
  // Add theme suggestion for all content types
  if (isset($variables['node'])) {
    if ($variables['node']->type != '') {
      $variables['theme_hook_suggestions'][] = 'page__node__' . $variables['node']->type;
    }
  }
}

/*
function hba_preprocess_views_view_list(&$vars) {
  hba_preprocess_views_view_unformatted($vars);
}
*/

/* Make page templates for specific content types. */
/* http://cheekymonkeymedia.ca/blog/brian-top-chimp/how-have-drupal-7-node-type-page-tpls */
//function hba_theme_preprocess_page(&$variables, $hook) {
function hba_theme_preprocess_page(&$variables) {
  // When this goes through the theme.inc some where it changes _ to - so the tpl name is actually page--type-typename.tpl
  if (isset($variables['node'])) {
    $variables['theme_hook_suggestions'][] = 'page__type__'. str_replace('_', '--', $variables['node']->type);  
  }

  // Move some variables to the top level for themer convenience and template cleanliness.
  if(drupal_is_front_page()) {
    $variables['title'] = '';
  }

  if( isset($variables['node']) && ($variables['node']->type == 'family' || $variables['node']->type == 'species' || $variables['node']->type == 'wikinote') ) {
    $variables['title'] = '';
  }

  // An user can't see someone else's birdlist or my record (except author and admins)
  /*if( isset($variables['node']) && ($variables['node']->type == 'locality' || $variables['node']->type == 'my_record') && ($variables['node']->uid !== $variables['user']->uid || !in_array('administrator', $variables['user']->roles)) ) {
    $variables['title'] = '';
    //$variables['head_title'] = ''; => would be $vars['head_title'], in a preprocess_html function
  }*/

  /*if(arg(0) == 'node' && arg(1) == '201985' && arg(2) == '') {
    $variables['title'] = '';
  }*/

  if (!user_is_logged_in() && arg(0) == 'user' && !arg(1)) {
    $variables['title'] = 'Sign in';
  }
  if (user_is_logged_in() && arg(0) == 'user' && arg(1) && !arg(2)) {
    $variables['title'] = '';
  }
  if (arg(0) == 'user' && arg(1) == 'password' && !arg(2)) {
    $variables['title'] = 'Forgot your password?';
  }
  if (arg(0) == 'user' && arg(1) == 'register' && !arg(2)) {
    $variables['title'] = 'Register';
  }

  // Generar un template per les entities "taxonomy"
  /*if (arg(0) == 'taxonomy' && arg(1) == 'term' ) {
    $term = taxonomy_term_load(arg(2));
    $vocabulary = taxonomy_vocabulary_load($term->vid);
    $variables['theme_hook_suggestions'][] = 'page__taxonomy_vocabulary_' . $vocabulary->machine_name;
  } // Funciona pero no es fa servir*/

  if (arg(0) == 'taxonomy' && arg(1) == 'term' && is_numeric(arg(2))) {
    unset($variables['page']['content']['content']['content']['system_main']['nodes']);
    unset($variables['page']['content']['content']['content']['system_main']['pager']);
  }

  drupal_add_library('hoverintent', 'hoverintent', TRUE);
}

/**
 * Implements theme_preprocess_node()
 */
function hba_theme_preprocess_node(&$vars) {
  // if user pictures are enabled on nodes, inject them with the body field
  if(isset($vars['user_picture']) && isset($vars['content']['body'][0]['#markup']) && !$vars['teaser']) {
    $vars['content']['body'][0]['#markup'] = $vars['user_picture'] . $vars['content']['body'][0]['#markup'];
  }
  // treure del camp Body els embeds videos, etc.
  /*if(isset($vars['content']['body'][0]['#markup']) && !$vars['teaser']) {
    $vars['content']['body'][0]['#markup'] = strip_only($vars['content']['body'][0]['#markup'], '<iframe> <embed>');
  }*/

  // Load anchorme library. http://alexcorvi.github.io/anchorme.js
  if( $vars['type'] == 'locality') {
    drupal_add_js('/sites/all/libraries/anchorme/anchorme.min.js', array('preprocess' => FALSE));
  }
}

/*
function hba_theme_form_comment_form_alter(&$form, &$form_state, &$form_id) {
  $form['comment_body']['#after_build'][] = 'configure_comment_form';
}

function configure_comment_form(&$form) {
  $form['und'][0]['format']['guidelines']['#access'] = FALSE;
  return $form;
} 
*/

/**
 * http://drupal.org/node/796530#comment-5116106
 * Inconsistent behavior with exposed filter in block AJAX
 * Se usa para poder fer funcionar els exposed filters de la view incrustada en los nodes Birdlist
 */
function hba_theme_form_views_exposed_form_alter(&$form, &$form_state){
  // Overrides the views exposed form url to be the current one
  // Avoids odd views redirect to views page from an exposed form in block
  // cas del bloc de les pagines /reference/xx: es perque utilitzem el mateix bloc (el de /reference/all) per les 3 pagines
  /*if ($form['#id'] == 'views-exposed-form-set-records-default') {
    $form['#action'] = request_uri();
  }*/
  if ($form['#id'] == 'views-exposed-form-myr-list-by-birdlist-default') {
    $form['#action'] = request_uri();
  }
  if ($form['#id'] == 'views-exposed-form-country-term-page-default') {
    $form['#action'] = request_uri().'#list';
  }
  /*if ($form['#id'] == 'views-exposed-form-my-record-page') {
    // Load anytime jquery plugin for the Date field
    drupal_add_css('/sites/all/libraries/anytime/anytime.compressed.css', array('preprocess' => FALSE));
    drupal_add_js('/sites/all/libraries/anytime/anytime.compressed.js', array('preprocess' => FALSE));
    // https://drupal.org/comment/6186888#comment-6186888
    //if(isset($form["field_loc_date_value"])): // the id of the view exposed filter
      //array_unshift($form["field_loc_date_value"]["#element_validate"],"hba_theme_date_views_select_validate");
    //endif;
  }*/
  if ($form['#id'] == 'views-exposed-form-taxonomy-term-for-journal-default') {
    $form['#action'] = request_uri();
  }

  if ($form['#id'] == 'sighting-custom-species-update-form' || $form['#id'] == 'views-exposed-form-sightings-bulk-change-species-page-1') { // views-exposed-form-sightings-bulk-change-species-page-1 en view original
    $form['#action'] = request_uri();
  }

  // Views Sightings: Display data i My birdlists: amagar les localitats de la IBC del filtre exposat "Territory"
  if($form['#id'] == 'views-exposed-form-sightings-page-1' || $form['#id'] == 'views-exposed-form-my-record-page'){
    if($form['#id'] == 'views-exposed-form-my-record-page'){
      global $user;
    }

    // Deixem 4922 World i 5535 Unknown territory, pels birdlists que poden tenir aquests territoris
    $result = "SELECT DISTINCT taxonomy_term_data.tid AS tid, taxonomy_term_data.name AS name
    FROM {taxonomy_term_data} taxonomy_term_data
    LEFT JOIN {taxonomy_vocabulary} taxonomy_vocabulary ON taxonomy_term_data.vid = taxonomy_vocabulary.vid
    LEFT JOIN {field_data_field_geo_territory_type} field_data_field_geo_territory_type ON taxonomy_term_data.tid = field_data_field_geo_territory_type.entity_id ";
    // Per la view dels birdlists, només mostrarem els territoris on l'usuari té birdlists
    if($form['#id'] == 'views-exposed-form-my-record-page'){
      $result .= "INNER JOIN {field_data_field_myr_country} c ON c.field_myr_country_target_id = taxonomy_term_data.tid
    INNER JOIN {node} n ON n.nid = c.entity_id AND n.uid=".$user->uid;
    }
    $result .= " WHERE (( (taxonomy_vocabulary.machine_name IN('geo')) )AND( (field_data_field_geo_territory_type.field_geo_territory_type_value BETWEEN '1' AND '2') OR (field_data_field_geo_territory_type.field_geo_territory_type_value = '4') )AND( (taxonomy_term_data.tid NOT IN('5520', '5521', '5523', '5522', '5524', '5525', '5526', '5527')) ))
    ORDER BY name ASC
    LIMIT 1000";

    $used_tids = db_query($result);

    // Buidem el select de les seves opcions
    unset($form['tid']['#options']);

    // Afegim al select buit cada territori que volem mostrar
    foreach($used_tids as $term){
      $form['tid']['#options'][$term->tid] = $term->name;
    }
  }
}

/**
 * Devuelve el mismo string que contiene un nombre dentro de parentesis pero con <em> englobando al parentesis
 * Entrada Ostrich (Struthio camelus)
 * Salida Ostrich <em>(Struthio camelus)</em>
 */
function _hba_cursive($string) {
  $string = str_replace("(", "<em>(", $string);
  $string = str_replace(")", ")</em>", $string);
  
  return $string;
}

function hba_theme_preprocess_html(&$variables) {
  // Miramos las columnas que hay
  if(isset($variables['page']['content']['content']['sidebar_third']) && hba_theme_region_not_empty($variables['page']['content']['content']['sidebar_third'])) {
    $variables['attributes_array']['class'][] = 'sidebar-third';
  }
  
  if(isset($variables['page']['content']['content']['sidebar_second']) && hba_theme_region_not_empty($variables['page']['content']['content']['sidebar_second'])) {
    $variables['attributes_array']['class'][] = 'sidebar-second';
  }
  
  if(isset($variables['page']['content']['content']['sidebar_first']) && hba_theme_region_not_empty($variables['page']['content']['content']['sidebar_first'])) {
    $variables['attributes_array']['class'][] = 'sidebar-first';
  }

  // Add an id in body tag of sighting/add and sighting/edit to help reduce nb of css elements
  //$variables['attributes_array']['id'] = $variables['xxx'] ? 'sighting-form' : '';
  //$variables['attributes_array']['class'][5]=='page-sighting-edit'
  if( isset($variables['page']['content']['content']['content']['system_main']['#form_id']) && ($variables['page']['content']['content']['content']['system_main']['#form_id']=='sighting_add_form' || $variables['page']['content']['content']['content']['system_main']['#form_id']=='sighting_edit_form') ) {
    $variables['attributes_array']['id'] = 'sighting-form';
  }
}

function hba_theme_region_not_empty($region) {
  foreach($region as $name => $block) {
    if(substr($name,0,1) != '#' && substr($name,0,11) != 'alpha_debug') {
      return true;
    }
  }

  return false;
}

function hba_theme_preprocess_search_result(&$vars) {
  // http://eureka.ykyuen.info/2011/11/04/drupal-add-image-to-apache-solr-search-result/ D6
  // Add node object to search-result.tpl.php
  //$n = $result['fields']['nid']['value'];
  /*$n = node_load($vars['result']['node']->nid);
  $n && ($vars['node'] = $n);*/

  // http://stackoverflow.com/questions/996253/how-to-customise-search-results-of-apachesolr-drupal-6
  //$vars['solr_search'] = views_embed_view('solr_search', 'default', $vars['result']['node']->nid);
  
  // http://www.acquia.com/resources/acquia-tv/conference/apache-solr-search-mastery
  
}

/**
 * implements hook_css_alter()
 */
function hba_theme_css_alter(&$css) {
  // Override the jquery.ui.dialog.css default file with a custom one
  if (isset($css['misc/ui/jquery.ui.dialog.css'])) {
    $css['misc/ui/jquery.ui.dialog.css']['data'] = drupal_get_path('theme', 'hba_theme') . '/css/jquery.ui/jquery-ui.dialog-custom.css';
  }
}
