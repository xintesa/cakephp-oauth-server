<?php
$this->viewVars['title_for_layout'] = __d('croogo', 'Clients');
$this->extend('/Common/admin_index');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Clients'), array('action' => 'index'));

?>

<div class="clients index">
	<table class="table table-striped">
	<tr>
		<th><?php echo $this->Paginator->sort('client_id'); ?></th>
		<th><?php echo $this->Paginator->sort('client_secret'); ?></th>
		<th><?php echo $this->Paginator->sort('name'); ?></th>
		<th><?php echo $this->Paginator->sort('redirect_uri'); ?></th>
		<th><?php echo $this->Paginator->sort('user_id'); ?></th>
		<th class="actions"><?php echo __d('croogo', 'Actions'); ?></th>
	</tr>
	<?php foreach ($clients as $client): ?>
	<tr>
		<td><?php echo h($client['Client']['client_id']); ?>&nbsp;</td>
		<td><?php echo h($client['Client']['client_secret']); ?>&nbsp;</td>
		<td><?php echo h($client['Client']['name']); ?>&nbsp;</td>
		<td><?php echo h($client['Client']['redirect_uri']); ?>&nbsp;</td>
		<td><?php echo h($client['User']['username']); ?>&nbsp;</td>
		<td class="item-actions">
			<?php echo $this->Croogo->adminRowAction('', array('action' => 'view', $client['Client']['client_id']), array('icon' => 'eye-open')); ?>
			<?php echo $this->Croogo->adminRowAction('', array('action' => 'edit', $client['Client']['client_id']), array('icon' => 'pencil')); ?>
			<?php echo $this->Croogo->adminRowAction('', array('action' => 'delete', $client['Client']['client_id']), array('icon' => 'trash'), __d('croogo', 'Are you sure you want to delete # %s?', $client['Client']['client_id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
