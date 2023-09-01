<?php declare(strict_types = 1);

namespace Drupal\test_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Faker\Factory;

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

    $fieldFilter = \Drupal::request()->query->get('fieldFilter') == '' ? 'name' : \Drupal::request()->query->get('fieldFilter');
    $filter = \Drupal::request()->query->get('filter') == '' ? '' : \Drupal::request()->query->get('filter');
    $amountRegister = \Drupal::request()->query->get('amount') ?? 10;
    $actualPage = \Drupal::request()->query->get('actualPage') ?? 1;
    
    $data = $this->database->select('test_module_register', 'TMR')->fields('TMR', []);
    if($filter) {
      $data->condition('TMR.'.$fieldFilter, $filter . '%', 'LIKE');
    }
    $data = $data->execute()->fetchAll();

    $totalPages = ceil(count($data) / $amountRegister);
    $nextPage = $actualPage == $totalPages ? 0 : $actualPage + 1;
    $previusPage = $actualPage == 1 ? 0 : $actualPage - 1;    
    
    $array = array_slice($data, (($amountRegister * $actualPage) - $amountRegister), (int)$amountRegister);
    $info = (object) [
      'amount' => count($array),
      'totalPages' => $totalPages,
      'nextPage' => $nextPage,
      'previusPage' => $previusPage,
      'actualPage' => $actualPage,
    ];
    $response = [$array, $info];
    return new JsonResponse($response);   
  }

  public function insertData(){

    $faker = Factory::create(Factory::DEFAULT_LOCALE);  
    $terms = [
      'CC',
      'TI',
      'PP',
      'PE',
    ];
    for ($i=0; $i < 250; $i++) { 
      $data = [      
          'name' => $faker->name,
          'lastname' => $faker->lastName,
          'document_type' => $terms[rand(0, 3)],
          'document' => mt_rand(1000000000, 9999999999),
          'email' => $faker->email,
          'phone' => mt_rand(1000000000, 9999999999),
          'country' => $faker->country,       
      ];   
      $this->database->insert('test_module_register')->fields($data)->execute();
    } 

    dd("listo");
  }
}
