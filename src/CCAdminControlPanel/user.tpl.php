<?php if($hasRoleAdmin): ?>

<h1>User Profile</h1>
<p>You can view and update this users profile.</p>
<?=$user_form?>

<?php else: ?>
<div class='alert'>You are not authorized to access this page.</div>
<?php endif; ?>
