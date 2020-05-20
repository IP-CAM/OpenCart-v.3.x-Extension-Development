<?php
class ControllerExtensionModuleOCBetterCharts extends Controller {
	const DEFAULT_COLOUR_SETTINGS = [
        'name' => 'Orders/Users Chart', // Name for db
		'Colour1' => '#FF0000', // Colour 1 HEX
		'Colour2' => '#00FF00', // Colour 2 HEX
		'Colour3' => '#FFFFFF', // Colour 3 HEX (Chart background)
		'status' => 1 // Module status (enabled by)
	];

    private $error = array();

    public function index() {
		$this->load->model('setting/module'); // Load admin setting/module model

		// If module_id != isset (if FALSE)
		if (!isset($this->request->get['module_id'])) {
			// Create new module in database
			$module_id = $this->model_setting_module->getModulesByCode('BetterCharts')[0]['module_id'];

			// Send back to this module to reload with module_id set
			$this->response->redirect($this->url->link('extension/module/OC_BetterCharts', '&user_token='.$this->session->data['user_token'].'&module_id='.$module_id));
		} else {
			// Trigger edit process
			$this->editModule($this->request->get['module_id']);
		}
	}

	private function addModule() {
		$this->load->model('setting/module'); // Load admin setting/module model
		$this->model_setting_module->addModule('BetterCharts', self::DEFAULT_COLOUR_SETTINGS); // Execute setting/setting addModule() 
		return $this->db->getLastId(); // Return last ID inserted into database
	}
    
    protected function editModule($module_id) {
		$this->load->model('setting/module'); // Load admin setting/module model
		$this->load->language('extension/module/OC_BetterCharts'); // Load language table

		// Set page title to local language heading
		$this->document->setTitle($this->language->get('heading_title'));

		// If a POST request is detected, and validate returns 'true'
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post); // Insert POST data into existing module in database [oc_modules]
			$this->session->data['success'] = $this->language->get('text_success'); // Pass defined success message to $data from language table
			// Redirect user to extensions page in dashboard
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		// Declare new array object 'data'
		$data = array();

		// Get module for $module_id
		$module_setting = $this->model_setting_module->getModule($module_id);
		



		// If 'Colour1' was passed with POST
		if (isset($this->request->post['Colour1'])) {
			$data['Colour1'] = $this->request->post['Colour1']; // Set $data['Colour1'] to value of POST['Colour1']
		} else {
			$data['Colour1'] = $module_setting['Colour1']; // Colour1 was not passed with POST, set to default
		}

		// If 'Colour2' was passed with POST
		if (isset($this->request->post['Colour2'])) {
			$data['Colour2'] = $this->request->post['Colour2']; // Set $data['Colour2'] to value of POST['Colour2']
		} else {
			$data['Colour2'] = $module_setting['Colour2']; // Colour2 was not passed with POST, set to default
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
		$htmlOutput = $this->load->view('extension/module/OC_BetterCharts', $data);

		// Execute HTML output as response
		$this->response->setOutput($htmlOutput);
	}
  
    public function validate() {
		// Check if Colour1 input is valid HEX
		if (!ctype_xdigit($this->request->post['Colour1'])) {
			$this->error['hex1'];
		}

		// Check if Colour2 input is valid HEX
		if (!ctype_xdigit($this->request->post['Colour2'])) {
			$this->error['hex2'];
		}
		
		// Return bool for errors detected (FALSE = validation failed)
		return empty($error);
	}
 
    public function install() {
		$this->load->model('setting/setting'); // Load admin setting/setting model
        $this->model_setting_setting->editSetting('module_OC_BetterCharts', ['module_OC_BetterCharts_status' => 1]); // Create setting database entry for 'module_OCBetterCharts'

		$module_id = $this->addModule();
	}

    public function uninstall() {
		$this->load->model('setting/setting'); // Load admin setting/setting model
        $this->model_setting_setting->deleteSetting('module_OC_BetterCharts'); // Delete setting database entry 'module_OCBetterCharts'
		
		$this->load->model('setting/module'); // Load admin setting/module model
		$this->model_setting_module->deleteModulesByCode('BetterCharts');
    }
}