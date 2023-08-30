<?php declare(strict_types = 1);

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Egulias\EmailValidator\Result\ValidEmail;

/**
 * Provides a Test module form.
 */
final class Formulario extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'test_module_formulario';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#required' => TRUE,
    ];

    $form['lastname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Apellido'),
      '#required' => TRUE,
    ];

    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree('types_documents');

    foreach($terms as $term) {
      $options[$term->name] = $term->name;
    }

    $form['document_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo de Documento'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['document'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Documento'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo Electronico'),
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'number',
      '#title' => $this->t('Telefono'),
      '#required' => TRUE,
    ];

    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree('countries');   

    foreach($terms as $term) {
      $optionsC[$term->name] = $term->name;
    }

    $form['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Pais'),
      '#required' => TRUE,
      '#options' => $optionsC,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    
    if(empty($form_state->getValue('name'))){
      $form_state->setErrorByName('name', 'El Nombre no puede ser vacio.');
    }

    if(empty($form_state->getValue('lastname'))){
      $form_state->setErrorByName('lastname', 'El Apellido no puede ser vacio.');
    }

    if(empty($form_state->getValue('document'))){
      $form_state->setErrorByName('document', 'El Documento no puede ser vacio.');
    }

    if(empty($form_state->getValue('phone'))){
      $form_state->setErrorByName('phone', 'El Telefono no puede ser vacio.');
    }
    
    if(empty($form_state->getValue('email')) || !filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)){
      $form_state->setErrorByName('email', 'El Documento no puede ser vacio.');
    }

    if(empty($form_state->getValue('document_type'))){
      $form_state->setErrorByName('document_type', 'Debe selecionar un Tipo de Documento');
    }

    if(empty($form_state->getValue('country'))){
      $form_state->setErrorByName('country', 'Debe selecionar un Pais');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    
    $data = [
      'name' => $form_state->getValue('name'),
      'lastname' => $form_state->getValue('lastname'),
      'document_type' => $form_state->getValue('document_type'),
      'document' => $form_state->getValue('document'),
      'email' => $form_state->getValue('email'),
      'phone' => $form_state->getValue('phone'),
      'country' => $form_state->getValue('country'),
    ];

    \Drupal::database()->insert('test_module_register')->fields($data)->execute();

    \Drupal::messenger()->addMessage($this->t('Datos almacenados correctamente'), 'status', TRUE);
  }

}
