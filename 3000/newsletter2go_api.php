<?php

require_once 'includes/application_top.php';
require_once dirname(__FILE__) . '/Nl2go_ResponseHelper.php';

class N2GoApi
{

    private $apikey;
    private $username;
    private $connected = false;
    private $userId;

    /**
     * @var Nl2go_ResponseHelper
     */
    private $responseHelper;

    /**
     * Associative array with get parameters
     * @var array
     */
    private $getParams;

    /**
     * Associative array with post parameters
     * @var array
     */
    private $postParams;

    public function __construct($action, $username, $apikey, $getParams = array(), $postParams = array())
    {
        header('Content-Type: application/json');
        $this->responseHelper = new Nl2go_ResponseHelper();
        if (tep_not_null($apikey) && tep_not_null($username)) {
            $this->apikey = $apikey;
            $this->username = $username;
            $this->getParams = $getParams;
            $this->postParams = $postParams;
            $this->connected = $this->checkApiKey();

            try {
                if (!$this->connected['success']) {
                    echo $this->responseHelper->generateErrorResponse($this->connected['message'], Nl2go_ResponseHelper::ERRNO_PLUGIN_CREDENTIALS_WRONG);
                } else {
                    switch ($action) {
                        case 'getCustomers':
                            echo $this->responseHelper->generateSuccessResponse($this->getCustomers());
                            break;
                        case 'getCustomerFields':
                            $fields = $this->getCustomerFields();
                            echo $this->responseHelper->generateSuccessResponse(array('fields' => $fields));
                            break;
                        case 'getCustomerCount':
                            echo $this->responseHelper->generateSuccessResponse($this->getCustomerCount());
                            break;
                        case 'unsubscribeCustomer':
                            if ($this->unsubscribeCustomer(0)) {
                                echo $this->responseHelper->generateSuccessResponse();
                            } else {
                                echo $this->responseHelper->generateErrorResponse('Unsubscribe customer failed!', 'int-2-404');
                            }

                            break;
                        case 'subscribeCustomer':
                            if ($this->unsubscribeCustomer(1)) {
                                echo $this->responseHelper->generateSuccessResponse();
                            } else {
                                echo $this->responseHelper->generateErrorResponse('Subscribe customer failed!', 'int-2-404');
                            }

                            break;
                        case 'getProduct':
                            echo $this->responseHelper->generateSuccessResponse($this->getProduct());
                            break;
                        case 'testConnection':
                            echo $this->responseHelper->generateSuccessResponse();
                            break;
                        case 'getLanguages':
                            echo $this->responseHelper->generateSuccessResponse($this->getLanguages());
                            break;
                        case 'getPluginVersion':
                            echo $this->responseHelper->generateSuccessResponse(array('version' => 3000));
                            break;
                        default:
                            echo $this->responseHelper->generateErrorResponse("Unknown action: $action", Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER);
                            break;
                    }

                    $this->logQuery($action);
                }
            } catch (Exception $exc) {
                echo $this->responseHelper->generateErrorResponse($exc->getMessage(), Nl2go_ResponseHelper::ERRNO_PLUGIN_OTHER);
            }
        } else {
            echo $this->responseHelper->generateErrorResponse('Credential missing', Nl2go_ResponseHelper::ERRNO_PLUGIN_CREDENTIALS_MISSING);
        }
    }

    public function getLanguages()
    {
        $languages = array();

        $langQuery = tep_db_query('SELECT code, name FROM ' . TABLE_LANGUAGES . ' ORDER BY sort_order ASC');
        $n = tep_db_num_rows($langQuery);
        for ($i = 0; $i < $n; $i++) {
            $lang = tep_db_fetch_array($langQuery);
            $languages[$lang['code']] = $lang['name'];
        }

        return array('languages' => $languages);
    }

    public function getCustomers()
    {
        $hours = (isset($this->getParams['hours']) ? tep_db_prepare_input($this->getParams['hours']) : '');
        $subscribed = (isset($this->getParams['subscribed']) ? tep_db_prepare_input($this->getParams['subscribed']) : '');
        $limit = (isset($this->getParams['limit']) ? tep_db_prepare_input($this->getParams['limit']) : '');
        $offset = (isset($this->getParams['offset']) ? tep_db_prepare_input($this->getParams['offset']) : '');
        $emails = (isset($this->postParams['emails']) ? tep_db_prepare_input($this->postParams['emails']) : '');
        $fields = (isset($this->postParams['fields']) ? tep_db_prepare_input($this->postParams['fields']) : '');
        $conditions = array();

        if (tep_not_null($hours)) {
            $time = date('Y-m-d H:i:s', time() - 3600 * $hours);
            $conditions[] = "ci.customers_info_date_account_last_modified >= '$time'";
        }

        if (tep_not_null($subscribed) && $subscribed) {
            $conditions[] = 'cu.customers_newsletter=' . $subscribed;
        }

        if (!empty($emails)) {
            $conditions[] = 'cu.customers_email_address IN (\'' . implode("','", $emails) . '\')';
        }

        $query = $this->buildCustomersQuery($fields) .
            ' FROM customers cu
                LEFT JOIN ' . TABLE_CUSTOMERS_INFO . ' ci ON cu.customers_id = ci.customers_info_id
                LEFT JOIN ' . TABLE_ADDRESS_BOOK . ' ab ON cu.customers_id = ab.customers_id
                LEFT JOIN ' . TABLE_COUNTRIES . ' co ON ab.entry_country_id = co.countries_id ';

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if (tep_not_null($limit)) {
            $offset = (tep_not_null($offset) ? $offset : 0);
            $query .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $customersQuery = tep_db_query($query);
        $customers = array();
        while ($customer = tep_db_fetch_array($customersQuery)) {
            $customers[] = $customer;
        }

        return array('customers' => $customers);
    }

    public function unsubscribeCustomer($status = 0)
    {
        $result = 0;
        $email = (isset($this->postParams['email']) ? tep_db_prepare_input($this->postParams['email']) :
            (isset($this->getParams['email']) ? tep_db_prepare_input($this->getParams['email']) : ''));

        if (tep_not_null($email)) {
            tep_db_query("UPDATE customers SET customers_newsletter = $status WHERE customers_email_address = '$email'");
            $result = tep_db_affected_rows();
        }

        return $result;
    }

    public function getProduct()
    {
        $id = isset($this->getParams['id']) ? tep_db_prepare_input($this->getParams['id']) : '';
        $lang = isset($this->getParams['lang']) ? tep_db_prepare_input($this->getParams['lang']) : '';

        if (!tep_not_null($id)) {
            return array('success' => false, 'message' => 'Invalid or missing parameters for getProduct request!');
        }

        if (!tep_not_null($lang)) {
            $langQuery = tep_db_query('SELECT code, name FROM ' . TABLE_LANGUAGES . ' ORDER BY sort_order ASC');
            $first = tep_db_fetch_array($langQuery);
            $lang = $first['code'];
        }

        $query = 'SELECT 
                pr.products_id as id, 
                pr.products_price as oldPrice, 
                pr.products_price as newPrice, 
                pr.products_price as oldPriceNet, 
                pr.products_price as newPriceNet, 
                pr.products_image as images, 
                pd.products_name as name, 
                pd.products_description as shortDescription, 
                pd.products_description as description, 
                pr.products_model as model, 
                mf.manufacturers_name as brand, 
                max(tr.tax_rate) as vat 
                FROM products pr 
                LEFT JOIN tax_rates tr ON pr.products_tax_class_id = tr.tax_class_id 
                LEFT JOIN manufacturers mf ON pr.manufacturers_id = mf.manufacturers_id 
                LEFT JOIN products_description pd ON pr.products_id = pd.products_id 
                LEFT JOIN languages ln ON pd.language_id = ln.languages_id '
            . "WHERE pr.products_id = $id AND ln.code = '$lang' GROUP BY pr.products_id";

        $productsQuery = tep_db_query($query);
        $product = tep_db_fetch_array($productsQuery);
        if ($product) {
            if ($product['vat']) {
                $product['oldPrice'] = $product['newPrice'] = $product['oldPriceNet'] * (1 + $product['vat'] * 0.01);
                $product['vat'] = round($product['vat'] * 0.01, 2);
            }

            $product['oldPriceNet'] = $product['newPriceNet'] = round($product['oldPriceNet'], 2);
            $product['oldPrice'] = $product['newPrice'] = round($product['oldPrice'], 2);

            $product['url'] = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
            $product['link'] = FILENAME_PRODUCT_INFO . '?products_id=' . $id;

            $product['images'] = ($product['images'] ? array(HTTP_SERVER . DIR_WS_HTTP_CATALOG . 'images/' . $product['images']) : array());
            $query = 'SELECT image FROM products_images WHERE products_id = ' . $id;
            $imagesQuery = tep_db_query($query);
            while ($image = tep_db_fetch_array($imagesQuery)) {
                $product['images'][] = HTTP_SERVER . DIR_WS_HTTP_CATALOG . 'images/' . $image['image'];
            }
        } else {
            return array('success' => false, 'message' => 'Invalid or missing parameters for getProduct request!');
        }

        return array('product' => $product);
    }

    /**
     * Returns json encode customer count based on group and subscribed parameters
     * @return string
     */
    public function getCustomerCount()
    {
        $subscribed = (isset($this->postParams['subscribed']) ? tep_db_prepare_input($this->postParams['subscribed']) :
            (isset($this->getParams['subscribed']) ? tep_db_prepare_input($this->getParams['subscribed']) : ''));

        $query = 'SELECT COUNT(*) as total FROM ' . TABLE_CUSTOMERS;
        if (tep_not_null($subscribed) && $subscribed == 1) {
            $query .= ' WHERE customers_newsletter = 1';
        }

        $totalQuery = tep_db_query($query);
        $result = tep_db_fetch_array($totalQuery);

        return array('customers' => $result['total']);
    }

    /**
     * Returns customer fields array
     * @return array
     */
    public function getCustomerFields()
    {
        $fields = array();
        $fields['cu.customers_id'] = $this->createField('cu.customers_id', 'Customer Id.', 'Integer');
        $fields['cu.customers_gender'] = $this->createField('cu.customers_gender', 'Gender');
        $fields['cu.customers_firstname'] = $this->createField('cu.customers_firstname', 'First name');
        $fields['cu.customers_lastname'] = $this->createField('cu.customers_lastname', 'Last name');
        $fields['cu.customers_dob'] = $this->createField('cu.customers_dob', 'Date of birth');
        $fields['cu.customers_email_address'] = $this->createField('cu.customers_email_address', 'E-mail address');
        $fields['cu.customers_telephone'] = $this->createField('cu.customers_telephone', 'Phone number');
        $fields['cu.customers_fax'] = $this->createField('cu.customers_fax', 'Fax');
        $fields['ci.customers_info_date_account_created'] = $this->createField('ci.customers_info_date_account_created', 'Date created');
        $fields['ci.customers_info_date_account_last_modified'] = $this->createField('ci.customers_info_date_account_last_modified', 'Date last modified');
        $fields['cu.customers_newsletter'] = $this->createField('cu.customers_newsletter', 'Subscribed', 'Boolean');
        $fields['ab.entry_company'] = $this->createField('ab.entry_company', 'Company');
        $fields['ab.entry_street_address'] = $this->createField('ab.entry_street_address', 'Street');
        $fields['ab.entry_city'] = $this->createField('ab.entry_city', 'City');
        $fields['co.countries_name'] = $this->createField('co.countries_name', 'Country');

        return $fields;
    }

    /**
     * Checks if there is an enabled user with given api key
     * @return array (
     *      'result'    =>   true|false,
     *      'message'   =>   result message,
     * )
     */
    private function checkApiKey()
    {
        $usersQuery = tep_db_query("SELECT * FROM newsletter2go_user WHERE apikey = '$this->apikey' AND email = '$this->username'");
        $user = tep_db_fetch_array($usersQuery);
        if (!empty($user)) {
            $this->userId = $user['id'];

            return $user['enabled'] ? array('success' => true) :
                array('success' => false, 'message' => 'Your API key has been revoked! Contact your system administrator.');
        }

        return array('success' => false, 'message' => 'Invalid API key! Contact your system administrator.');
    }

    private function logQuery($info)
    {
        $info = tep_db_prepare_input($info);
        tep_db_query("INSERT INTO newsletter2go_log (user_id, info) VALUES($this->userId, '$info')");
    }

    /**
     * Helper function to create field array
     * @param $id
     * @param $name
     * @param string $type
     * @param string $description
     * @return array
     */
    private function createField($id, $name, $type = 'String', $description = '')
    {
        return array('id' => $id, 'name' => $name, 'description' => $description, 'type' => $type);
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildCustomersQuery($fields = array())
    {
        $select = array();
        if (empty($fields)) {
            $fields = array_keys($this->getCustomerFields());
        } else if (!in_array('cu.customers_id', $fields)) {
            //customer Id must always be present
            $fields[] = 'cu.customers_id';
        }

        foreach ($fields as $field) {
                $select[] = "$field AS '$field'";
        }

        return 'SELECT ' . implode(', ', $select);
    }

}

$user = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : (isset($_POST['username']) ? $_POST['username'] : ''));
$apikey = (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : (isset($_POST['apiKey']) ? $_POST['apiKey'] : ''));
$action = isset($_GET['action']) ? $_GET['action'] : '';

$api = new N2GoApi($action, $user, $apikey, $_GET, $_POST);