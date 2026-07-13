<?php

declare(strict_types=1);

$addon = rex_addon::get('osmproxy');

$form = rex_config_form::factory($addon->getName());
$form->addFieldset($addon->i18n('config'));

$providerField = $form->addSelectField('default_provider');
$providerField->setLabel($addon->i18n('default_provider'));
$providerField->setNotice($addon->i18n('default_provider_notice'));
$providerSelect = $providerField->getSelect();
$providerSelect->setSize(1);

foreach (array_keys(\FriendsOfREDAXO\OsmProxy\Providers::all()) as $providerKey) {
    $providerSelect->addOption($providerKey, $providerKey);
}

$referrerField = $form->addSelectField('allow_remote_referrer_check');
$referrerField->setLabel($addon->i18n('allow_remote_referrer_check'));
$referrerField->setNotice($addon->i18n('allow_remote_referrer_check_notice'));
$referrerSelect = $referrerField->getSelect();
$referrerSelect->setSize(1);
$referrerSelect->addOption($addon->i18n('yes'), '1');
$referrerSelect->addOption($addon->i18n('no'), '0');

$vectorField = $form->addSelectField('show_vector_examples');
$vectorField->setLabel($addon->i18n('show_vector_examples'));
$vectorField->setNotice($addon->i18n('show_vector_examples_notice'));
$vectorSelect = $vectorField->getSelect();
$vectorSelect->setSize(1);
$vectorSelect->addOption($addon->i18n('yes'), '1');
$vectorSelect->addOption($addon->i18n('no'), '0');

$assetHostsField = $form->addTextAreaField('asset_hosts');
$assetHostsField->setLabel($addon->i18n('asset_hosts'));
$assetHostsField->setNotice($addon->i18n('asset_hosts_notice'));
$assetHostsField->setAttribute('rows', '6');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
