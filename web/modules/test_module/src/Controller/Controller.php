<?php declare(strict_types = 1);

namespace Drupal\test_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for test_module routes.
 */
final class Controller extends ControllerBase {

  protected $database;

  /**
   * The controller constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $connection) {
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container){
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
    );
  }

  public function data() {

    $data = $this->database->select('test_module_register', 'TMR')->fields('TMR', [])->execute()->fetchAll();

    $rows = [];
    foreach ($data as $item) {
      $rows[] = [
        'data' => [
          'id' => $item->id,
          'name' => $item->name,
          'lastname' => $item->lastname,
          'document_type' => $item->document_type,
          'document' => $item->document,
          'email' => $item->email,
          'phone' => $item->phone,
          'country' => $item->country,
        ],
      ];
    }      
    

    $table = [
      '#theme' => 'table',
      '#header' => [
          'id' => 'ID',
          'name' => 'Nombre',
          'lastname' => 'Apellido',
          'document_type' => 'Tipo de documento',
          'document' => 'Documento',
          'email' => 'Correo electronico',
          'phone' => 'Telefono',
          'country' => 'Pais',
        ],
      '#rows' => $rows,
      '#empty' => $this->t('No hay elementos para mostrar.'),
      // '#attributes' => [
      //   'cellpadding' => '10',
      //   'border' => '1',
      //   'text-align' => 'center'
      // ],
      // '#style' => 'vertical-align: middle; border-width: 1px; border-style: solid;',
    ];

    $form['table'] = $table;
    
    return  $form;
  }

  public function endPointData() {

    $fieldFilter = \Drupal::request()->query->get('fieldFilter') ?? 'name';
    $filter = \Drupal::request()->query->get('filter') ?? '';

    $data = $this->database->select('test_module_register', 'TMR')->fields('TMR', []);
    if($filter) {
      $data->condition('TMR.'.$fieldFilter, $filter . '%', 'LIKE');
    }
    $data = $data->execute()->fetchAll();

    $actualPage = \Drupal::request()->query->get('page') ?? 0;
    $page = [
      '0' => 10,
      '1' => 50,
      '2' => 100,
    ];
    
    $array = array_slice($data, 0, $page[$actualPage]);
    
    return new JsonResponse($array);   
  }
}
