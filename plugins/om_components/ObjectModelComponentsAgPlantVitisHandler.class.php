<?php

class ObjectModelComponentsAgPlantVitisHandler extends ObjectModelComponentsDefaultHandler {
  public $growth_stage_dates = array();
  public $growth_stages = array();
  
  public function __construct($options = array()) {
    parent::__construct($options);
    $this->growth_stages = array(
      'bud_break' => 'Bud Break to Pre-Bloom', 
      'pre_bloom' => 'Pre Bloom', 
      'bloom' => 'Bloom', 
      'critical_time' => 'Critical Time', 
      'bunch_closure' => 'Bunch Closure', 
      'veraison' => 'Veraison', 
      'pre_harvest' => 'Pre-Harvest', 
      'post_harvest' => 'Post-Harvest',
    );
    $this->growth_stage_dates = array();
    $mo = date('n');
    if (isset($options['yr'])) {
      $yr = $options['yr'];
    } else {
      // guess what year we are looking at
      $yr = date('Y');
      if ($mo >= 11) {
        $yr++;
      }
    }
    $opt = $this->options['growth_stages'];
    $begin = date('Y-m-d', strtotime("$opt[bud_break_week] Monday of $opt[bud_break_month] $yr"));
    $dobj = new DateTime($begin);
    $swks = 0;
    foreach ($this->growth_stages as $stage => $label) {
      $dobj->modify("+$swks weeks");
      // set the stage weeks after, so the next stage date is incremented
      $swks = intval($this->options['growth_stages'][$stage]);
      $eobj = clone $dobj;
      $eobj->modify("+$swks weeks");
      $eobj->modify("-1 days");
      $this->growth_stage_dates[$stage] = array(
        'begin' => $dobj->format('Y-m-d'),
        'end' => $eobj->format('Y-m-d'),
        'weeks' => $swks,
      );
    }
    $this->state['growth_stage_dates'] = $this->growth_stage_dates;
  }
  
  function DefineOptions() {
    $options = parent::DefineOptions();
    $options['growth_stages'] = array(
      'default' => array(
        'bud_break_month' => 'April',
        'bud_break_week' => 'third',
        'bud_break' => 3, 
        'pre_bloom' => 4, 
        'bloom' => 2, 
        'critical_time' => 6, 
        'bunch_closure' => 2, 
        'veraison' => 2, 
        'pre_harvest' => 4, 
        'post_harvest' => 4,
      ),
    );
    return $options;
  }
  
  // when we go to D8 this will be relevant
    // public function buildOptionsForm(&$form, FormStateInterface $form_state) {
  // until then, we use the old school method
  // need to change this to buildForm and leave buildOptionsForm for config in panels/blocks
  public function buildOptionsForm(&$form, $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $hidden = array();
    foreach ($hidden as $hidethis) {
      $form[$hidethis]['#type'] = 'hidden';
    }
    // enter SQL in text field
    $form['growth_stages'] = array(
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => 'Growth Stages',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => 'Growth Stages',
    );
    $mopt = array();
    for($m = 1;$m <= 12; $m++){ 
      $month =  date("F", mktime(0, 0, 0, $m)); 
      $mopt[$month] = $month;
    }
    $form['growth_stages']['bud_break_month'] = array(
      '#title' => t('Month of Bud Break'),
      '#description' => t('Approximate Month of Bud Break.'),
      '#type' => 'select',
      '#options' => $mopt,
      '#required' => TRUE,
      '#default_value' => $this->options['growth_stages']['bud_break_month'],
    );
    $wopt = array(
      'first' => 'First',
      'second' => 'Second',
      'third' => 'Third',
      'fourth' => 'Fourth',
      'fifth' => 'Fifth',
    );
    $form['growth_stages']['bud_break_week'] = array(
      '#title' => t('Week of Bud Break' . " (" . $this->growth_stage_dates['bud_break']['begin'] . ")"),
      '#description' => t('Approximate Week of Bud Break (in month).'),
      '#type' => 'select',
      '#options' => $wopt,
      '#required' => TRUE,
      '#default_value' => $this->options['growth_stages']['bud_break_week'],
    );
    $wopt = array();
    for($w = 1;$w <= 10; $w++){ 
      $wopt[$w] = $w;
    }
    $stages = $this->growth_stages;
    foreach ($stages as $key => $label) {
      $form['growth_stages'][$key] = array(
        '#title' => t($label . " (" . $this->growth_stage_dates[$key]['begin'] . ")"),
        '#type' => 'select',
        '#options' => $wopt,
        '#required' => TRUE,
        '#default_value' => $this->options['growth_stages'][$key],
      );
    }    
  }
  
}
?>