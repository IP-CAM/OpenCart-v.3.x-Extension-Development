<?php
class ControllerExtensionModuleAgeRestriction extends Controller {
	const DEFAULT_MODULE_SETTINGS = [
		'name' => 'Age Restriction (21)', // Module name
		'message' => 'Are you %s and older?', // Modal message string
		'age' => 21, // Module required age
		'redirect_url' => 'http://www.example.org',
		'status' => 1 // Module status
	];

    private $error = array();
  
    public function index() {
		// If module_id is not set (isset returns FALSE)
		if (!isset($this->request->get['module_id'])) {
			// Create new module in database
			$module_id = $this->addModule();

			// Set response -> link( module_path, GET args )
			$this->response->redirect($this->url->link('extension/module/age_restriction', '&user_token='.$this->session->data['user_token'].'&module_id='.$this->db->getLastId()));
		} else { // module_id is already set
			$this->editModule($this->request->get['module_id']);
		}
	}

	// Create new module in db
	private function addModule() {
		// Load admin setting/module model
		$this->load->model('setting/module');

		// Execute setting/setting addModule() 
		$this->model_setting_module->addModule('age_restriction', self::DEFAULT_MODULE_SETTINGS);
		
		// Return last ID inserted into database
		return $this->db->getLastId();
	}

	// Modify existing module in db
	protected function editModule($module_id) {
		$this->load->model('setting/module'); // Load admin setting/module model
		$this->load->language('extension/module/age_restriction'); // Load language table

		// Set page title to heading
		$this->document->setTitle($this->language->get('heading_title'));

		// If a POST request is detected, and validate returns 'true', do this
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			// Edit database entry for module (module_id), set to value of POST request
			$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			
			// Set string of 'success' message to localised string from the /language/ file
			$this->session->data['success'] = $this->language->get('text_success');
			
			// Set response -> link( module_path, GET args )
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data = array();

		// Get module for $module_id
		$module_setting = $this->model_setting_module->getModule($module_id);

		// If 'name' was passed with POST
		if (isset($this->request->post['name'])) {
			// Set $data['name'] to value of POST['name']
			$data['name'] = $this->request->post['name'];
		} else {
			// Name was not passed with POST, set to default (declared at top of this document)
			$data['name'] = $module_setting['name'];
		}

		// If 'message' was passed with POST
		if (isset($this->request->post['message'])) {
			// Set $data['message'] to value of POST['message']
			$data['message'] = $this->request->post['message'];
		} else {
			// Message was not passed with POST, set to default (declared at top of this document)
			$data['message'] = $module_setting['message'];
		}

		// If 'redirect_url' was passed with POST
		if (isset($this->request->post['redirect_url'])) {
			// Set $data['redirect_url'] to value of POST['redirect_url']
			$data['redirect_url'] = $this->request->post['redirect_url'];
		} else {
			// Redirect_url was not passed with POST, set to default (declared at top of this document)
			$data['redirect_url'] = $module_setting['redirect_url'];
		}
		
		// If 'age' was passed with POST
		if (isset($this->request->post['age'])) {
			// Set $data['age'] to value of POST['age']
			$data['age'] = $this->request->post['age'];
		} else {
			// Age was not passed with POST, set to default (declared at top of this document)
			$data['age'] = $module_setting['age'];
		}
		
		// If 'status' was passed with POST
		if (isset($this->request->post['status'])) {
			// Set $data['status'] to value of POST['status']
			$data['status'] = $this->request->post['status'];
		} else {
			// Status was not passed with POST, set to default (declared at top of this document)
			$data['status'] = $module_setting['status'];
		} 

		// Declare value of form actions ('cancel' & 'save')
		$data['action']['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module');
		$data['action']['save'] = "";

		// Set value of error for response
		$data['error'] = $this->error;	

		// Load page elements as members of $data (to display in view)
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		// Declare HTML output
		$htmlOutput = $this->load->view('extension/module/age_restriction', $data);

		// Execute HTML output as response
		$this->response->setOutput($htmlOutput);
	}
  
    public function validate() {
		// Check if user has permission to perform these actions
		if (!$this->user->hasPermission('modify', 'extension/module/age_restriction')) {
			$this->error['permission'] = true;
			return false;
		}
		
		// Check if name returns string length, if not record error
		if (!utf8_strlen($this->request->post['name'])) {
			$this->error['name'] = true;
		}

		// Check if message returns string length, if not record error
		if (!utf8_strlen($this->request->post['message'])) {
			$this->error['message'] = true;
		}

		// Check if redirect_url returns string length, if not record error
		if (!utf8_strlen($this->request->post['redirect_url'])) {
			$this->error['redirect_url'] = true;
		}
		
		// Check if age returns string length, if not record error
		if (!is_numeric($this->request->post['age'])) {
			$this->error['age'] = true;
		}
		
		// Return BOOL whether error is empty or not (true if no errors detected while validating)
		return empty($this->error);
	}
 
    public function install() {
		// Load admin setting/setting model
		$this->load->model('setting/setting');

		// Create setting database entry for 'module_age_restriction'
		$this->model_setting_setting->editSetting('module_age_restriction', ['module_age_restriction_status' => 1]);
	}

    public function uninstall() {
		// Load admin setting/setting model
		$this->load->model('setting/setting');

		// Delete setting database entry 'module_age_restriction'
		$this->model_setting_setting->deleteSetting('module_age_restriction');
	}
}