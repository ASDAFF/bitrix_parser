<?
class parser_interface(){
    public static function OnAdminListDisplayHandler(&$list) {
		$strCurPage = $GLOBALS['APPLICATION']->GetCurPage();
		$bElemPage = ($strCurPage=='/bitrix/admin/iblock_element_admin.php' ||
						$strCurPage=='/bitrix/admin/cat_product_admin.php'
					);
		$bSectPage = ($strCurPage=='/bitrix/admin/iblock_section_admin.php' ||
						$strCurPage=='/bitrix/admin/cat_section_admin.php'
					);
		$bMixPage = ($strCurPage=='/bitrix/admin/iblock_list_admin.php');
		$bRightPage = ($bElemPage || $bSectPage || $bMixPage);

		if ($bRightPage && CModule::IncludeModule('iblock')) {
			if (strlen(CASDiblock::$error)) {
				$message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => CASDiblock::$error));
				echo $message->Show();
			}

			$lAdmin = new CAdminList($list->table_id, $list->sort);

			$IBLOCK_ID = intval($_REQUEST['IBLOCK_ID']);
			$find_section = intval($_REQUEST['find_section_section']);
			if ($find_section < 0)
				$find_section = 0;

			$boolSectionCopy = CASDIblockRights::IsSectionSectionCreate($IBLOCK_ID, $find_section);
			$boolElementCopy = CASDIblockRights::IsSectionElementCreate($IBLOCK_ID, $find_section);

			if ($bSectPage) {
				if ($boolSectionCopy) {
					foreach ($list->aRows as $id => $v) {
						$arnewActions = array();
						foreach ($v->aActions as $i => $act) {
							$arnewActions[] = $act;
							if ($act['ICON'] == 'edit') {
								$arnewActions[] = array('ICON' => 'copy',
														'TEXT' => GetMessage('ASD_ACTION_POPUP_COPY'),
														'ACTION' => $lAdmin->ActionDoGroup($v->id, 'asd_copy_in_list',
																	'&type='.urlencode($_REQUEST['type']).'&lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section),
														);
							}
						}
						$v->aActions = $arnewActions;
					}
				}
			} 
		}
	}
}
?>