<?
$first_name = array(
	'name' => 'first_name',
	'value' => set_value('first_name'),
	'maxlength' => $this->config->item('username_max_length', 'tank_auth'),
	'size' => 30
);
$last_name = array(
    'name' => 'last_name',
    'value' => set_value('last_name'),
    'maxlength' => $this->config->item('username_max_length', 'tank_auth'),
    'size' => 30
);
$dob = array(
    'name' => 'dob',
    'value' => set_value('dob'),
    'maxlength' => 80,
    'size' => 30
);
$gender = array(
    'name' => 'gender', 
    'value' => set_value('gender') 
);
$email = array(
	'name' => 'email',
	'value'	=> set_value('email'),
	'maxlength' => 80,
	'size' => 30
);
$password = array(
	'name' => 'password',
	'value' => set_value('password'),
	'maxlength' => $this->config->item('password_max_length', 'tank_auth'),
	'size' => 30
);
$confirm_password = array(
	'name' => 'confirm_password',
	'value' => set_value('confirm_password'),
	'maxlength'	=> $this->config->item('password_max_length', 'tank_auth'),
	'size' => 30
);
$captcha = array(
	'name' => 'captcha',
	'maxlength' => 8
);

$dob_m[''] = 'mm';
for($i = 1; $i <= 12; $i++)
    $dob_m[$i] = $i;
$dob_d[''] = 'dd';
for($i = 1; $i <= 31; $i++)
    $dob_d[$i] = $i;
$dob_y[''] = 'year';
for($i = date('Y'); $i >= 1940; $i--)
    $dob_y[$i] = $i;
    
$submit = array(
    'value' => 'Continue',
    'class' => 'bt bt_pink'
);
?>

<?= form_open($this->uri->uri_string()) ?>
<table>
	<tr>
		<td><?= form_label('First name', $first_name['name']) ?></td>
		<td><?= form_input($first_name) ?> <?= form_error($first_name['name']) ?><?= isset($errors[$first_name['name']])?$errors[$first_name['name']]:'' ?></td>
	</tr>
    <tr>
        <td><?= form_label('Last name', $last_name['name']) ?></td>
        <td><?= form_input($last_name) ?> <?= form_error($last_name['name']) ?><?= isset($errors[$last_name['name']])?$errors[$last_name['name']]:'' ?></td>
    </tr>
	<tr>
		<td><?= form_label('Date of birth', $dob['name']) ?></td>
		<td><?= form_dropdown('dob_m', $dob_m, set_value('dob_m')) ?> <?= form_dropdown('dob_d', $dob_d, set_value('dob_d')) ?> <?= form_dropdown('dob_y', $dob_y, set_value('dob_y')) ?> <?= form_error($dob['name']) ?><?= isset($errors[$dob['name']])?$errors[$dob['name']]:'' ?></td>
	</tr>
	<tr>
		<td><?= form_label('Gender', $gender['name']) ?></td>
        <td><?= form_radio('gender', 'male', (set_value('gender') == 'male') ? TRUE : FALSE) ?> <?= form_label('Male') ?> <?= form_radio('gender', 'female', (set_value('gender') == 'female') ? TRUE : FALSE) ?> <?= form_label('Female') ?> <?= form_error($gender['name']) ?><?= isset($errors[$gender['name']])?$errors[$gender['name']]:'' ?></td>
	</tr>

	<? if ($captcha_registration) {
		if ($use_recaptcha) { ?>
	<tr>
		<td colspan="2">
			<div id="recaptcha_image"></div>
		</td>
		<td>
			<a href="javascript:Recaptcha.reload()">Get another CAPTCHA</a>
			<div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">Get an audio CAPTCHA</a></div>
			<div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">Get an image CAPTCHA</a></div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="recaptcha_only_if_image">Enter the words above</div>
			<div class="recaptcha_only_if_audio">Enter the numbers you hear</div>
		</td>
		<td><input type="text" id="recaptcha_response_field" name="recaptcha_response_field" /> <?= form_error('recaptcha_response_field') ?></td>
		<?= $recaptcha_html ?>
	</tr>
	<? } else { ?>
	<tr>
		<td colspan="3">
			<p>Enter the code exactly as it appears:</p>
			<?= $captcha_html ?>
		</td>
	</tr>
	<tr>
		<td><?= form_label('Confirmation Code', $captcha['id']) ?></td>
		<td><?= form_input($captcha) ?> <?= form_error($captcha['name']) ?></td>
	</tr>
	<? }
	} ?>
</table>

<table>
    <tr>
        <td><?= form_label('Email Address', $email['name']) ?></td>
        <td><?= form_input($email) ?> <?= form_error($email['name']) ?><?= isset($errors[$email['name']])?$errors[$email['name']]:'' ?></td>
    </tr>
    <tr>
        <td><?= form_label('Password', $password['name']) ?></td>
        <td><?= form_password($password) ?> <?= form_error($password['name']) ?></td>
    </tr>
    <tr>
        <td><?= form_label('Confirm Password', $confirm_password['name']) ?></td>
        <td><?= form_password($confirm_password) ?> <?= form_error($confirm_password['name']) ?></td>
    </tr>
</table>

<?= form_submit($submit) ?>
<?= form_close() ?>