<?php
namespace Bitsand\builtin\Controller;

use Bitsand\Controllers\Controller;

class ErrorMethodNotAllowed extends Controller {
	public function index() {
		$this->data['file_name'] = '';

		$this->setView('error/method_not_allowed', 'ErrorMethodNotAllowed');

		$this->view->setOutput($this->render());
	}
}