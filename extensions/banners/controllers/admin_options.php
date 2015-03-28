<?php if ( ! defined('BASEPATH')) exit('No direct access allowed');

class Admin_options extends Extension {

	public function options($options = array()) {
		$this->load->library('user');
		$this->load->model('Extensions_model');
		$this->load->model('Design_model');

		if (!$this->user->islogged()) {
  			redirect('login');
		}

    	if (!$this->user->hasPermissions('access', 'banners')) {
  			redirect('permission');
		}

		if ($this->session->flashdata('alert')) {
			$data['alert'] = $this->session->flashdata('alert');
		} else {
			$data['alert'] = '';
		}

		extract($options);

		$ext_name 			= strtolower($name);
		$data['title'] 		= $title;

		if (empty($ext_data) OR !is_array($ext_data)) {
			$ext_data = array();
		}

		if ($this->input->post('layouts')) {
			$ext_data['layouts'] = $this->input->post('layouts');
		}

		$data['modules'] = array();
		if (!empty($ext_data['layouts'])) {
			foreach ($ext_data['layouts'] as $module) {

				$data['modules'][] = array(
					'banner_id'		=> isset($module['banner_id']) ? $module['banner_id']: '',
					'layout_id'		=> isset($module['layout_id']) ? $module['layout_id']: '',
					'position' 		=> isset($module['position']) ? $module['position']: '',
					'priority' 		=> isset($module['priority']) ? $module['priority']: '',
					'status' 		=> isset($module['status']) ? $module['status']: ''
				);
			}
		}

		$data['banners'] = array();
		$results = $this->Design_model->getBanners();
		foreach ($results as $result) {
			$data['banners'][] = array(
				'banner_id'		=> $result['banner_id'],
				'name'			=> $result['name']
			);
		}

		$data['layouts'] = array();
		$results = $this->Design_model->getLayouts();
		foreach ($results as $result) {
			$data['layouts'][] = array(
				'layout_id'		=> $result['layout_id'],
				'name'			=> $result['name']
			);
		}

		$_GET['extension_id'] = $extension_id;
		if ($this->input->post() AND $this->_updateModule() === TRUE) {
			if ($this->input->post('save_close') === '1') {
				redirect('extensions');
			}

			redirect('extensions/edit?action=edit&name=banners');
		}

		$this->render($data);
	}

	public function _updateModule() {
    	if (!$this->user->hasPermissions('modify', 'banners')) {
			$this->alert->set('warning', 'Warning: You do not have permission to update!');
			return TRUE;
    	} else if ($this->validateForm() === TRUE) {
			$update = array();

			$update['type'] 			= 'module';
			$update['name'] 			= $this->input->get('name');
			$update['title'] 			= $this->input->post('title');
			$update['extension_id'] 	= (int) $this->input->get('extension_id');
			$update['data']['layouts'] 	= $this->input->post('layouts');

			if ($this->Extensions_model->updateExtension($update, '1')) {
				$this->alert->set('success', 'Banners module updated sucessfully.');
			} else {
				$this->alert->set('warning', 'An error occured, nothing updated.');
			}

			return TRUE;
		}
	}

 	public function validateForm() {
        $this->form_validation->set_rules('title', 'Title', 'xss_clean|trim|required|min_length[2]|max_length[128]');

        if ($this->input->post('layouts'))
        {
            foreach ($this->input->post('layouts') as $key => $value)
            {
                $this->form_validation->set_rules('layouts[' . $key . '][banner_id]', 'Banner', 'xss_clean|trim|required|integer');
                $this->form_validation->set_rules('layouts[' . $key . '][layout_id]', 'Layout', 'xss_clean|trim|required|integer');
                $this->form_validation->set_rules('layouts[' . $key . '][position]', 'Position', 'xss_clean|trim|required');
                $this->form_validation->set_rules('layouts[' . $key . '][priority]', 'Priority', 'xss_clean|trim|integer');
                $this->form_validation->set_rules('layouts[' . $key . '][status]', 'Status', 'xss_clean|trim|required|integer');
            }
        }

		if ($this->form_validation->run() === TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

/* End of file banners.php */
/* Location: ./extensions/banners/controllers/banners.php */