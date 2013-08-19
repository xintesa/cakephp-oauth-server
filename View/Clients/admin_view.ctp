<?php
$this->viewVars['title_for_layout'] = sprintf('%s: %s', __d('croogo', 'Clients'), h($client['Client']['client_id']));

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Clients'), array('action' => 'index'));
	
?>
<h2 class="hidden-desktop"><?php echo __d('croogo', 'Client'); ?></h2>

<div class="row-fluid">
	<div class="span12 actions">
		<ul class="nav-buttons">
		<li><?php echo $this->Html->link(__d('croogo', 'Edit Client'), array('action' => 'edit', $client['Client']['client_id']), array('button' => 'default')); ?> </li>
		<li><?php echo $this->Form->postLink(__d('croogo', 'Delete Client'), array('action' => 'delete', $client['Client']['client_id']), array('button' => 'default'), __d('croogo', 'Are you sure you want to delete # %s?', $client['Client']['client_id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('croogo', 'List Clients'), array('action' => 'index'), array('button' => 'default')); ?> </li>
		<li><?php echo $this->Html->link(__d('croogo', 'New Client'), array('action' => 'add'), array('button' => 'default')); ?> </li>
		</ul>
	</div>
</div>

<div class="clients view">
	<dl class="inline">
		<dt><?php echo __d('croogo', 'Client Id'); ?></dt>
		<dd>
			<?php echo h($client['Client']['client_id']); ?>
			&nbsp;
		</dd>
		<?php if ($isAdmin): ?>
		<dt><?php echo __d('croogo', 'Client Secret'); ?></dt>
		<dd>
			<?php echo h(OAuthUtility::decrypt($client['Client']['client_secret'])); ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<dt><?php echo __d('croogo', 'Name'); ?></dt>
		<dd>
			<?php echo h($client['Client']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'Redirect Uri'); ?></dt>
		<dd>
			<?php echo h($client['Client']['redirect_uri']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'User Id'); ?></dt>
		<dd>
			<?php echo h($client['Client']['user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'Created By'); ?></dt>
		<dd>
			<?php echo h($client['Client']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'Created'); ?></dt>
		<dd>
			<?php echo h($client['Client']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'Modified By'); ?></dt>
		<dd>
			<?php echo h($client['Client']['modified_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __d('croogo', 'Modified'); ?></dt>
		<dd>
			<?php echo h($client['Client']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
