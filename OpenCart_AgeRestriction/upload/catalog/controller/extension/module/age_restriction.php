<?php
class ControllerExtensionModuleAgeRestriction extends Controller {

	public function index($setting = null) {
        // Load localisation settings
        $this->load->language('extension/module/age_restriction');

        // If setting is set, and contains the index 'status'
        if ($setting && $setting['status']) {
			// Declare new array object 'data'
            $data = array();

            if (isset($this->session->data['module_age_restriction_pass']) && $this->session->data['module_age_restriction_pass'] < $setting['age']) {
                $show_modal = true;
            }
            
            if (!isset($this->session->data['module_age_restriction_pass'])) {
                $show_modal = true;
            }

            if (isset($show_modal) && $show_modal) {
                // Set message data
                $data['message'] = sprintf($setting['message'], $setting['age']);
                $data['age'] = $setting['age'];
                $data['redirect_url'] = $setting['redirect_url'];
                $data['session_redirect'] = $this->url->link('extension/module/age_restriction/startAgeSession');

                // Return value of $data to catalog/view/extension/module/age_restriction
                return $this->load->view('extension/module/age_restriction', $data);
            }
		}
    }
    
    public function startAgeSession() { //ajax
        // set 'module_age_restriction_pass' to value of POST['age']
		$this->session->data['module_age_restriction_pass'] = $this->request->post['age'];

        // If SESSION 'module_age_restriction_pass' is set
		if (isset ($this->session->data['module_age_restriction_pass'] )) {
            // If POST['age'] is more than SESSION ['module_age_restriction_pass']
			if ($this->request->post['age'] > $this->session->data['module_age_restriction_pass']) {
                // Set SESSION 'module_age_restriction_pass' to value of GET['age']
				$this->session->data['module_age_restriction_pass'] = $this->request->get['age'] ;
			} 
		} else {
            // Set SESSION 'module_age_restriction_pass' to POST['age']
			$this->session->data['module_age_restriction_pass'] = $this->request->post['age'];
		}

		$data = array();
		$data['success'] = true;
		$this->response->setOutput($this->load->view('extension/module/age_restriction_session', $data));
	}
}