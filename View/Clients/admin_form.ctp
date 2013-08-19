<?php
$this->viewVars['title_for_layout'] = __d('croogo', 'Clients');
$this->extend('/Common/admin_edit');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Clients'), array('action' => 'index'));

if ($this->action == 'admin_edit') {
	$this->Html->addCrumb($this->data['Client']['client_id'], '/' . $this->request->url);
	$this->viewVars['title_for_layout'] = 'Clients: ' . $this->data['Client']['client_id'];
} else {
	$this->Html->addCrumb(__d('croogo', 'Add'), '/' . $this->request->url);
}

echo $this->Form->create('Client');

?>
<div class="clients row-fluid">
	<div class="span8">
		<ul class="nav nav-tabs">
		<?php
			echo $this->Croogo->adminTab(__d('croogo', 'Client'), '#client');
			echo $this->Croogo->adminTabs();
		?>
		</ul>

		<div class="tab-content">
			<div id='client' class="tab-pane">
			<?php
				echo $this->Form->input('client_id');
				$this->Form->inputDefaults(array('label' => false, 'class' => 'span10'));
				echo $this->Form->input('name', array(
					'label' => 'Name',
				));
				if ($this->request->params['action'] == 'edit'):
					echo $this->Form->input('client_secret', array(
						'label' => 'Client Secret',
					));
				endif;
				echo $this->Form->input('redirect_uri', array(
					'label' => 'Redirect Uri',
				));
				$username = isset($this->data['User']['username']) ? $this->data['User']['username'] : null;
				echo $this->Form->autocomplete('user_id', array(
					'type' => 'text',
					'label' => 'User Id',
					'default' => $username,
					'autocomplete' => array(
						'data-displayField' => 'username',
						'data-primaryKey' => 'id',
						'data-queryField' => 'name',
						'data-relatedElement' => '#ClientUserId',
						'data-url' => '/api/v1.0/users/lookup.json',
					),
				));
				echo $this->Croogo->adminTabs();
			?>
			</div>
		</div>

	</div>

	<div class="span4">
	<?php
		echo $this->Html->beginBox(__d('croogo', 'Publishing')) .
			$this->Form->button(__d('croogo', 'Apply'), array('name' => 'apply')) .
			$this->Form->button(__d('croogo', 'Save'), array('class' => 'btn btn-primary')) .
			$this->Html->link(__d('croogo', 'Cancel'), array('action' => 'index'), array('class' => 'btn btn-danger')) .
			$this->Html->endBox();
		?>
	</div>

</div>
<?php echo $this->Form->end(); ?>
