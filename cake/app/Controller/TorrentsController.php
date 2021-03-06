<?php
App::uses('AppController', 'Controller');
dbconn();
loggedinorreturn();
parked();
/**
 * Torrents Controller
 *
 * @property Torrent $Torrent
 * @property RequestHandlerComponent $RequestHandler
 */
class TorrentsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('RequestHandler');

/**
 * index method
 *
 * @return void
 */
	/* public function index() { */
	/* 	$this->Torrent->recursive = 0; */
	/* 	$this->set('torrents', $this->paginate()); */
	/* } */

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Torrent->id = $id;
		if (!$this->Torrent->exists()) {
			throw new NotFoundException(__('Invalid torrent'));
		}
		$torrent = $this->Torrent->read(['id', 'name', 'promotion_time_type', 'promotion_until', 'sp_state', 'pos_state', 'picktype', 'oday'], $id);
#		echo h(json_encode($torrent));
		$this->set('torrent', $torrent);
		$this->set('_serialize', 'torrent');
	}

/**
 * add method
 *
 * @return void
 */
/*	public function add() {
		if ($this->request->is('post')) {
			$this->Torrent->create();
			if ($this->Torrent->save($this->request->data)) {
				$this->Session->setFlash(__('The torrent has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The torrent could not be saved. Please, try again.'));
			}
		}
		$tcategories = $this->Torrent->Tcategory->find('list');
		$this->set(compact('tcategories'));
		}*/

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Torrent->id = $id;
		if (!$this->Torrent->exists()) {
			throw new NotFoundException(__('Invalid torrent'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		  $data = $this->request->data;
		  $d = ['Tcategory' => ['Tcategory' => $data['Tcategory']['Tcategory']]];

		  $keys = ['id'];

		  if (checkPrivilege(['Torrent', 'oday'])) {
		    $keys[] = 'oday';
		  }

		  if (checkPrivilege(['Torrent', 'pr'])) {
		    array_push($keys, 'sp_state', 'promotion_time_type', 'promotion_until');
		  }

		  if (checkPrivilege(['Torrent', 'sticky'])) {
		    $keys[] = 'pos_state';
		  }

		  foreach ($keys as $key) {
		    if (isset($data['Torrent'][$key])) {
		      $d['Torrent'][$key] = $data['Torrent'][$key];
		    }
		  }

			if ($this->Torrent->save($d)) {
			  $tcategories = [];
			  foreach ($this->Torrent->read(null, $id)['Tcategory'] as $tc) {
			    $tc = $this->Torrent->Tcategory->read(null, $tc['id'])['Tcategory'];
			    $tcategories[] = ['id' => $tc['id'], 'name' => $tc['name'], 'showName' => $tc['showName'], 'hidden' => $tc['hidden']];
			  }
			  
			  $result = ['success' => true, 'messgae' => __('The torrent has been saved'), 'tcategories' =>$tcategories];
			  $this->Session->setFlash(__('The torrent has been saved'));
#			  $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The torrent could not be saved. Please, try again.'));
				$result = ['success' => false, 'message' => __('The torrent could not be saved. Please, try again.')];
			}
		} else {
			$this->request->data = $this->Torrent->read(null, $id);
			$result = ['success' => false, 'messgae' => __('GET method not allowed')];
		}
		$tcategories = $this->Torrent->Tcategory->find('list');
		$this->set(['tcategories' => $tcategories,
			    'result' => $result]);

		$this->set('_serialize', 'result');
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
/*	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Torrent->id = $id;
		if (!$this->Torrent->exists()) {
			throw new NotFoundException(__('Invalid torrent'));
		}
		if ($this->Torrent->delete()) {
			$this->Session->setFlash(__('Torrent deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Torrent was not deleted'));
		$this->redirect(array('action' => 'index'));
		}*/
}
