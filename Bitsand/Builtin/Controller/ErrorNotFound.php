<?php
namespace Bitsand\builtin\Controller;

use Bitsand\Controllers\Controller;

class ErrorNotFound extends Controller {
	public function index() {
		$this->data['file_name'] = '';

		$this->setView('error/not_found', 'ErrorNotFound');

		$this->view->setOutput($this->render());
	}
}