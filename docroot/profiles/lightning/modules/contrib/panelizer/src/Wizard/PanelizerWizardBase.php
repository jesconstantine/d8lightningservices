<?php

/**
 * @file
 * Contains \Drupal\panelizer\Wizard\PanelizerWizardBase.
 */

namespace Drupal\panelizer\Wizard;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\panelizer\Access\PanelizerUIAccess;
use Drupal\panelizer\Form\PanelizerWizardContentForm;
use Drupal\panelizer\Form\PanelizerWizardContextForm;
use Drupal\panelizer\Form\PanelizerWizardGeneralForm;

abstract class PanelizerWizardBase extends FormWizardBase {

  /**
   * {@inheritdoc}
   */
  protected function customizeForm(array $form, FormStateInterface $form_state) {
    $form = parent::customizeForm($form, $form_state);
    $cached_values = $form_state->getTemporaryValue('wizard');
    // Get the current form operation.
    $operation = $this->getOperation($cached_values);
    $operations = $this->getOperations($cached_values);
    $default_operation = reset($operations);
    if ($operation['form'] == $default_operation['form']) {
      // Create id and label form elements.
      $form['name'] = array(
        '#type' => 'fieldset',
        '#attributes' => array('class' => array('fieldset-no-legend')),
        '#title' => $this->getWizardLabel(),
      );
      $form['name']['label'] = array(
        '#type' => 'textfield',
        '#title' => $this->getMachineLabel(),
        '#required' => TRUE,
        '#size' => 32,
        '#default_value' => !empty($cached_values['label']) ? $cached_values['label'] : '',
        '#maxlength' => 255,
        '#disabled' => !empty($cached_values['label']),
      );
      $form['name']['id'] = array(
        '#type' => 'machine_name',
        '#maxlength' => 128,
        '#machine_name' => array(
          'source' => array('name', 'label'),
        ),
        '#description' => $this->t('A unique machine-readable name for this display. It must only contain lowercase letters, numbers, and underscores.'),
        '#default_value' => !empty($cached_values['id']) ? $cached_values['id'] : '',
        '#disabled' => !empty($cached_values['id']),
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getWizardLabel() {
    return $this->t('Wizard Information');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineLabel() {
    return $this->t('Wizard name');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $operations = [
      'general' => [
        'form' => PanelizerWizardGeneralForm::class,
        'title' => $this->t('General settings'),
      ],
      'contexts' => [
        'form' => PanelizerWizardContextForm::class,
        'title' => $this->t('Contexts'),
      ],
    ];

    // Add any wizard operations from the plugin itself.
    foreach ($cached_values['plugin']->getWizardOperations($cached_values) as $name => $operation) {
      $operations[$name] = $operation;
    }

    // Change the class that manages the Content step.
    if (isset($operations['content'])) {
      //$operations['content']['form'] = PanelizerWizardContentForm::class;
    }

    return $operations;
  }

  public function initValues() {
    $cached_values = parent::initValues();
    $cached_values['access'] = new PanelizerUIAccess();
    if (empty($cached_values['plugin'])) {
      /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $plugin */
      $plugin = \Drupal::service('plugin.manager.display_variant')->createInstance('panels_variant');
      $plugin->setPattern('panelizer');
      $plugin->setBuilder('ipe');
      $plugin->setStorage('panelizer_default', 'TEMPORARY_STORAGE_ID');
      $cached_values['plugin'] = $plugin;
    }
    if (empty($cached_values['contexts'])) {
      $cached_values['contexts'] = [];
    }
    return $cached_values;
  }


  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    // Save the panels display mode and its custom settings as third party
    // data of the display mode for this entity+bundle+display.
    /** @var \Drupal\panelizer\Panelizer $panelizer */
    $panelizer = \Drupal::service('panelizer');
    list($entity_type, $bundle, $view_mode, $display_id) = explode('__', $cached_values['id']);
    $panelizer->setDefaultPanelsDisplay($display_id, $entity_type, $bundle, $view_mode, $cached_values['plugin']);
    $panelizer->setDisplayStaticContexts($display_id, $entity_type, $bundle, $view_mode, $cached_values['contexts']);

    parent::finish($form, $form_state);
    $form_state->setRedirect('panelizer.wizard.edit', ['machine_name' => $cached_values['id']]);
  }

  /**
   * Wraps the context mapper.
   *
   * @return \Drupal\ctools\ContextMapperInterface
   */
  protected function getContextMapper() {
    return \Drupal::service('ctools.context_mapper');
  }

  /**
   * {@inheritdoc}
   */
  protected function getContexts($cached_values) {
    return $this->getContextMapper()->getContextValues($cached_values['contexts']);
  }

}
