<?php
require_once ROOT_DIR . '/Action.php';

class RBdigitalMagazine_AccessOnline extends Action
{
	/** @var RBdigitalMagazineDriver $recordDriver */
	private $recordDriver;
	function launch()
	{
		global $interface;

		$id = strip_tags($_REQUEST['id']);
		$interface->assign('id', $id);
		require_once ROOT_DIR . '/RecordDrivers/RBdigitalMagazineDriver.php';
		$this->recordDriver = new RBdigitalMagazineDriver($id);

		$user = UserAccount::getLoggedInUser();

		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				if (!$this->recordDriver->isValid()) {
					$this->display('../Record/invalidRecord.tpl', 'Invalid Record', '');
					die();
				}

				//Do the redirection
				require_once ROOT_DIR . '/Drivers/RBdigitalDriver.php';
				$rbdigitalDriver = new RBdigitalDriver();
				$rbdigitalDriver->redirectToRBdigitalMagazine($patron, $this->recordDriver);
				//We don't actually get to here since the redirect happens above
				die();
			} else {
				AspenError::raiseError('Sorry, it looks like you don\'t have permissions to access this title.');
			}
		} else {
			AspenError::raiseError('You must be logged in to access this title.');
		}
	}

	function getBreadcrumbs() : array
	{
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'My Account');
		$breadcrumbs[] = new Breadcrumb($this->recordDriver->getRecordUrl(), $this->recordDriver->getTitle());
		$breadcrumbs[] = new Breadcrumb('', 'Access Online');
		return $breadcrumbs;
	}
}