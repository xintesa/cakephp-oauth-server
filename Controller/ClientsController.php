<?php

App::uses('OAuthAppController', 'OAuth.Controller');

/**
 * Clients Controller
 *
 * @property Client $Client
 */
class ClientsController extends OAuthAppController {

/**
 * beforeFilter
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->loadModel('Users.Role');
		$this->Role->Behaviors->attach('Croogo.Aliasable');
		$isAdmin = $this->Session->read('Auth.User.role_id') == $this->Role->byAlias('admin');
		$this->set(compact('isAdmin'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Client->recursive = 0;
		$this->set('clients', $this->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Client->exists($id)) {
			throw new NotFoundException(__d('croogo', 'Invalid client'));
		}
		$options = array('conditions' => array('Client.' . $this->Client->primaryKey => $id));
		$this->set('client', $this->Client->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Client->create();
			if ($this->Client->add($this->request->data)) {
				$this->Session->setFlash(__d('croogo', 'The client has been saved: %s', $this->Client->addClientSecret), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('croogo', 'The client could not be saved. Please, try again.'), 'default', array('class' => 'error'));
			}
		}
		$users = $this->Client->User->find('list');
		$this->set(compact('users'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Client->exists($id)) {
			throw new NotFoundException(__d('croogo', 'Invalid client'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Client->save($this->request->data)) {
				$this->Session->setFlash(__d('croogo', 'The client has been saved'), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('croogo', 'The client could not be saved. Please, try again.'), 'default', array('class' => 'error'));
			}
		} else {
			$options = array('conditions' => array('Client.' . $this->Client->primaryKey => $id));
			$this->request->data = $this->Client->find('first', $options);
		}
		$users = $this->Client->User->find('list');
		$this->set(compact('users'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @throws MethodNotAllowedException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Client->id = $id;
		if (!$this->Client->exists()) {
			throw new NotFoundException(__d('croogo', 'Invalid client'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->Client->delete()) {
			$this->Session->setFlash(__d('croogo', 'Client deleted'), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__d('croogo', 'Client was not deleted'), 'default', array('class' => 'error'));
		$this->redirect(array('action' => 'index'));
	}
}
