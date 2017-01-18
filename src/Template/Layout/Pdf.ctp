<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$user = $this->request->Session()->read('Auth')['User'];
?>

<link href="<?= $this->request->webroot; ?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/plugins/font-awesome/css/font-awesome.min.css rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="<?= $this->request->webroot; ?>assets/global/css/components-rounded.css" id="style_components" rel="stylesheet" type="text/css"/>

<script src="<?= $this->request->webroot; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?= $this->request->webroot; ?>assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="<?= $this->request->webroot; ?>assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="<?= $this->request->webroot; ?>js/common.js" type="text/javascript"></script>

<?= $this->fetch('content') ?>
