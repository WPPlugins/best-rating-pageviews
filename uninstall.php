<?php
if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;
// �������� �������� �������. ������� �� ���� ������� ����� � ��� ���������.
function brpv_delete_plugin() {
	global $wpdb; // ���������� ����� wordpress ��� ������ � ��

	delete_option('brpv_version');
	delete_option('brpv_debug'); // �������� ����� �������
	delete_option('brpv_not_count_bots'); // ����� �� ���������� �����
}
brpv_delete_plugin(); // �������� ������� �������� �������
?>