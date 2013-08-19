<?php

App::uses('OAuthUtility', 'OAuth.Lib');
App::uses('Shell', 'Console');
App::uses('Validation', 'Utility');

/**
 * Client utility shell class
 */
class ClientsShell extends Shell {

/**
 * Models used by this shell
 */
	public $uses = array(
		'OAuth.Client',
	);

/**
 * Configure option parser
 */
	public function getOptionParser() {
		return parent::getOptionParser()
			->description('Client Utility')
			->addSubCommand('list', array(
				'help' => 'List existing client records',
				'parser' => array(
					'options' => array(
						'secret' => array(
							'help' => 'Display secrets',
							'short' => 's',
							'boolean' => true,
						),
					),
				),
			))
			->addSubCommand('add', array(
				'help' => 'Add a new client',
				'parser' => array(
					'options' => array(
						'name' => array(
							'required' => true,
							'help' => 'Client Name',
							'short' => 'n',
						),
						'redirect_uri' => array(
							'required' => true,
							'help' => 'Redirect URI',
							'short' => 'u',
						),
					),
				),
			));
	}

/**
 * Shell entry point
 */
	public function main() {
		$method = null;
		if (isset($this->args[0])) {
			$method = $this->args[0];
		}

		switch ($method) {
			case 'list':
				$this->_clients();
			break;

			default:
				$this->_displayHelp();
			break;
		}
	}

/**
 * List all client records
 */
	protected function _clients() {
		$clients = $this->Client->find('all', array(
			'recursive' => -1,
		));
		$this->out("");
		foreach ($clients as $data) {
			$client = $data['Client'];
			$this->out(sprintf('%-15s: %s', 'Client Id', $client['client_id']));
			$this->out(sprintf('%-15s: %s', 'Client Name', $client['name']));
			if ($this->params['secret']) {
				$secret = OAuthUtility::decrypt($client['client_secret']);
				$this->out(sprintf('%-15s: %s', 'Client Secret', $secret));
			}
			$this->out(sprintf('%-15s: %s', 'Redirect URI', $client['redirect_uri']));
			$this->out("");
		}
		$this->out(sprintf('%d record(s) found', count($clients)));
	}

/**
 * Add a new client record
 */
	public function add() {
		if (empty($this->params['name'])) {
			return $this->error('Please provide `name`');
		}
		if (empty($this->params['redirect_uri'])) {
			return $this->error('Please provide `redirect_uri`');
		}
		if (!Validation::url($this->params['redirect_uri'])) {
			return $this->error('Please provide a valid `redirect_uri`');
		}
		$client = $this->Client->create(array(
			'name' => $this->params['name'],
			'redirect_uri' => $this->params['redirect_uri'],
		));
		$client = $this->Client->add($client);
		if (!$client) {
			return $this->error('Unable to add client record');
		}
		$this->out("Client successfully added:\n");
		$this->out(sprintf("\tClient id: %s", $client['Client']['client_id']));
		$this->out(sprintf("\tClient name: %s", $client['Client']['name']));
		$this->out(sprintf("\tClient secret: %s", $this->Client->addClientSecret));
		$this->out();
	}

}
