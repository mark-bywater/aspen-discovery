<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';

class DataObjectUtil
{
	/**
	 * Get the edit form for a data object based on the structure of the object
	 *
	 * @param $objectStructure array representing the structure of the object.
	 *
	 * @return string and HTML Snippet representing the form for display.
	 */
	static function getEditForm($objectStructure){
		global $interface;

		//Define the structure of the object.
		$interface->assign('structure', $objectStructure);
		//Check to see if the request should be multipart/form-data
		$contentType = null;
		foreach ($objectStructure as $property){
			if ($property['type'] == 'image' || $property['type'] == 'file'){
				$contentType = 'multipart/form-data';
			}
		}
		$interface->assign('contentType', $contentType);
		$interface->assign('formLabel', 'Edit ' . $contentType);
		return  $interface->fetch('DataObjectUtil/objectEditForm.tpl');
	}

	/**
	 * Save the object to the database (and optionally solr) based on the structure of the object
	 * Takes care of determining whether or not the object is new or not.
	 *
	 * @param array $structure The structure of the data object
	 * @param string $dataType The class of the data object
	 * @return array
	 */
	static function saveObject($structure, $dataType)
	{
		//Check to see if we have a new object or an exiting object to update
		/** @var DataObject $object */
		$object = new $dataType();
		DataObjectUtil::updateFromUI($object, $structure);
		$primaryKey = $object->__primaryKey;
		$primaryKeySet = !empty($object->$primaryKey);

		$validationResults = DataObjectUtil::validateObject($structure, $object);
		$validationResults['object'] = $object;

		if ($validationResults['validatedOk']){
			//Check to see if we need to insert or update the object.
			//We can tell which to do based on whether or not the primary key is set

			if ($primaryKeySet){
				$result = $object->update();
				$validationResults['saveOk'] = ($result == 1);
			}else{
				$result = $object->insert();
				$validationResults['saveOk'] = $result;
			}
			if (!$validationResults['saveOk']){
				$error = $object->getLastError();
				if (isset($error)){
					$validationResults['errors'][] = 'Save failed ' . $error;
				}else{
					$validationResults['errors'][] = 'Save failed';
				}
			}
		}
		return $validationResults;
	}

	/**
	 * Delete an object from the database (and optionally solr).
	 *
	 * @param $dataObject
	 * @param $form
	 */
	static function deleteObject($structure, $dataType){

	}

	/**
	 * Validate that the inputs for the data object are correct prior to saving the object.
	 *
	 * @param $dataObject
	 * @param $object - The object to validate
	 *
	 * @return array Results of validation
	 */
	static function validateObject($structure, $object){
		//Setup validation return array
		$validationResults = array(
		  'validatedOk' => true,
		  'errors' => array(),
		);

		//Do the validation
		foreach ($structure as $property){
			$value = isset($_REQUEST[$property['property']]) ? $_REQUEST[$property['property']] : null;
			if (isset($property['required']) && $property['required'] == true){
				if ($value == null && strlen($value) > 0){
					$validationResults['errors'][] = $property['property'] . ' is required.';
				}
			}
			if ($property['type'] == 'password' || $property['type'] == 'storedPassword'){
				$valueRepeat = isset($_REQUEST[$property['property'].'Repeat']) ? $_REQUEST[$property['property'].'Repeat'] : null;
				if ($value != $valueRepeat) {
					$validationResults['errors'][] = $property['property'] . ' does not match ' . $property['property'] . 'Repeat';
				}
			}

			//Check to see if there is a custom validation routine
			if (isset($property['serverValidation'])) {
				$validationRoutine = $property['serverValidation'];
				$propValidation = $object->$validationRoutine();
				if ($propValidation['validatedOk'] == false) {
					$validationResults['errors'] = array_merge($validationResults['errors'], $propValidation['errors']);
				}
			}
		}

		//Make sure there aren't errors
		if (count($validationResults['errors']) > 0){
			$validationResults['validatedOk'] = false;
		}
		return $validationResults;
	}

	static function updateFromUI($object, $structure){
		foreach ($structure as $property){
			DataObjectUtil::processProperty($object, $property);
		}
	}

	static function processProperty($object, $property){
		global $logger;
		$propertyName = $property['property'];
		if ($property['type'] == 'section'){
			foreach ($property['properties'] as $subProperty){
				DataObjectUtil::processProperty($object, $subProperty);
			}
		}else if (in_array($property['type'], array('regularExpression'))){
			if (isset($_REQUEST[$propertyName])){
				$object->setProperty($propertyName, trim($_REQUEST[$propertyName]), $property);
			} else {
				$object->setProperty($propertyName, "", $property);
			}

		}else if (in_array($property['type'], array('text', 'enum', 'hidden', 'url', 'email', 'multiemail'))){
			if (isset($_REQUEST[$propertyName])){
				if ($object instanceof UnsavedDataObject && $property['type'] == 'enum'){
					$object->setProperty($propertyName, $property['values'][$_REQUEST[$propertyName]], $property);
				}else{
					$newValue = strip_tags(trim($_REQUEST[$propertyName]));
					if ($newValue != null) {
						$newValue = preg_replace('/\x{2029}/usm', '', $newValue);
					}
					$object->setProperty($propertyName, $newValue, $property);
				}
			} else {
				$object->setProperty($propertyName, "", $property);
			}

		}else if (in_array( $property['type'], array('textarea', 'html', 'markdown', 'javascript', 'folder', 'crSeparated'))){
			if (empty($_REQUEST[$propertyName]) || strlen(trim($_REQUEST[$propertyName])) == 0){
				$object->setProperty($propertyName, "", $property);
			}else{
				$object->setProperty($propertyName, trim($_REQUEST[$propertyName]), $property);
			}
			//Strip tags from the input to avoid problems
			if ($property['type'] == 'textarea' || $property['type'] == 'crSeparated'){
				$object->setProperty($propertyName, strip_tags($object->$propertyName), $property);
			}elseif ($property['type'] != 'javascript'){
				$systemVariables = SystemVariables::getSystemVariables();
				if ($systemVariables != false){
					if (!empty($systemVariables->allowHtmlInMarkdownFields)){
						$allowableTags = '<' . implode('><', explode('|', $systemVariables->allowableHtmlTags)). '>';
					}else{
						$allowableTags = null;
					}
				}else{
					if (!empty($property['allowableTags'])){
						$allowableTags = $property['allowableTags'];
					}else{
						$allowableTags = '<p><a><b><em><ul><ol><em><li><strong><i><br>';
					}
				}

				if (!empty($allowableTags)) {
					$object->setProperty($propertyName, strip_tags($object->$propertyName, $allowableTags), $property);
				}else{
					$object->setProperty($propertyName, $object->$propertyName, $property);
				}
			}
		}else if ($property['type'] == 'timestamp'){
			if (empty($_REQUEST[$propertyName])){
				$object->setProperty($propertyName, 0, $property);
			}else{
				try {
					$timeValue = new DateTime($_REQUEST[$propertyName]);
					$object->setProperty($propertyName, $timeValue->getTimestamp(), $property);
				}catch (Exception $e){
					//Could not load the timestamp
					$object->setProperty($propertyName, 0, $property);
				}
			}
		}else if ($property['type'] == 'integer'){
			if (preg_match('/\\d+/', $_REQUEST[$propertyName])){
				$object->setProperty($propertyName, $_REQUEST[$propertyName], $property);
			}else{
				$object->setProperty($propertyName, 0, $property);
			}
		} else if ($property['type'] == 'color' || $property['type'] == 'font') {
			$defaultProperty = $propertyName . 'Default';
			if (isset($_REQUEST[$propertyName . '-default']) && ($_REQUEST[$propertyName . '-default'] == 'on')) {
				$object->setProperty($defaultProperty, 1, null);
			} else {
				$object->setProperty($defaultProperty, 0, null);
			}
			$object->setProperty($propertyName, $_REQUEST[$propertyName], $property);
		}else if ($property['type'] == 'currency'){
			if (preg_match('/\\$?\\d*\\.?\\d*/', $_REQUEST[$propertyName])){
				if (substr($_REQUEST[$propertyName], 0, 1) == '$'){
					$object->setProperty($propertyName, substr($_REQUEST[$propertyName], 1), $property);
				}else{
					$object->setProperty($propertyName, $_REQUEST[$propertyName], $property);
				}
			}else{
				$object->setProperty($propertyName, 0, $property);
			}

		}else if ($property['type'] == 'checkbox'){
			$object->setProperty($propertyName,isset($_REQUEST[$propertyName]) && $_REQUEST[$propertyName] == 'on' ? 1 : 0, $property);

		}else if ($property['type'] == 'multiSelect'){
			if (isset($_REQUEST[$propertyName]) && is_array($_REQUEST[$propertyName])){
				$object->setProperty($propertyName, $_REQUEST[$propertyName], $property);
			}else{
				$object->setProperty($propertyName, [], $property);
			}

		}else if ($property['type'] == 'date'){
			if (empty(strlen($_REQUEST[$propertyName])) || strlen($_REQUEST[$propertyName]) == 0 || $_REQUEST[$propertyName] == '0000-00-00'){
				$object->setProperty($propertyName, null, $property);
			}else{
				$dateParts = date_parse($_REQUEST[$propertyName]);
				$time = $dateParts['year'] . '-' . $dateParts['month'] . '-' . $dateParts['day'];
				$object->setProperty($propertyName, $time, $property);
			}

		}else if ($property['type'] == 'partialDate'){
			$dayField = $property['propNameDay'];
			$object->setProperty($dayField, $_REQUEST[$dayField], null);
			$monthField = $property['propNameMonth'];
			$object->setProperty($monthField, $_REQUEST[$monthField], null);
			$yearField = $property['propNameYear'];
			$object->setProperty($yearField, $_REQUEST[$yearField], null);

		}else if ($property['type'] == 'image'){
			//Make sure that the type is correct (jpg, png, or gif)
			if (isset($_REQUEST["remove{$propertyName}"])){
				$object->setProperty($propertyName, '', $property);

			}else if (isset($_FILES[$propertyName])){
				if (isset($_FILES[$propertyName]["error"]) && $_FILES[$propertyName]["error"] == 4){
					$logger->log("No file was uploaded for $propertyName", Logger::LOG_DEBUG);
					//No image supplied, use the existing value
				}else if (isset($_FILES[$propertyName]["error"]) && $_FILES[$propertyName]["error"] > 0){
					//return an error to the browser
					$logger->log("Error in file upload for $propertyName", Logger::LOG_ERROR);
				}else if (in_array($_FILES[$propertyName]["type"], array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml'))){
					$logger->log("Processing uploaded file for $propertyName", Logger::LOG_DEBUG);
					//Copy the full image to the files directory
					//Filename is the name of the object + the original filename
					global $configArray;
					if (isset($property['storagePath'])){
						$destFileName = $_FILES[$propertyName]["name"];
						$destFolder = $property['storagePath'];
						$destFullPath = $destFolder . '/' . $destFileName;
						$copyResult = copy($_FILES[$propertyName]["tmp_name"], $destFullPath);
						$logger->log("Copied file to $destFullPath result: $copyResult", Logger::LOG_DEBUG);
					}else{
						$logger->log("Creating thumbnails for $propertyName", Logger::LOG_DEBUG);
						if (isset($property['path'])){
							$destFolder = $property['path'];
							$destFileName = $_FILES[$propertyName]["name"];
							if (!file_exists($destFolder)){
								mkdir($destFolder, 0755, true);
							}
							$pathToThumbs = $destFolder . '/thumbnail';
							$pathToMedium = $destFolder . '/medium';
						}else{
							$destFileName = $propertyName . $_FILES[$propertyName]["name"];
							$destFolder = $configArray['Site']['local'] . '/files/original';
							$pathToThumbs = $configArray['Site']['local'] . '/files/thumbnail';
							$pathToMedium = $configArray['Site']['local'] . '/files/medium';
						}

						$destFullPath = $destFolder . '/' . $destFileName;
						$copyResult = copy($_FILES[$propertyName]["tmp_name"], $destFullPath);

						if ($copyResult){
							$img = imagecreatefromstring(file_get_contents($destFullPath));
							$width = imagesx( $img );
							$height = imagesy( $img );

							if (isset($property['thumbWidth'])) {
								//Create a thumbnail if needed
								$thumbWidth = $property['thumbWidth'];
								if ($width > $thumbWidth) {
									$new_width = $thumbWidth;
									$new_height = floor($height * ($thumbWidth / $width));

									// create a new temporary image
									$tmp_img = imagecreatetruecolor($new_width, $new_height);
									imagealphablending($tmp_img, false);
									imagesavealpha($tmp_img, true);
									$transparent = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
									imagefilledrectangle($tmp_img, 0, 0, $width, $height, $transparent);

									// copy and resize old image into new image
									imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

									// save thumbnail into a file
									imagepng($tmp_img, "{$pathToThumbs}/{$destFileName}");
								} else {
									copy($destFullPath, "{$pathToThumbs}/{$destFileName}");
								}

							}
							if (isset($property['mediumWidth'])) {
								//Create a thumbnail if needed
								$thumbWidth = $property['mediumWidth'];
								if ($width > $thumbWidth) {
									$new_width = $thumbWidth;
									$new_height = floor($height * ($thumbWidth / $width));

									// create a new temporary image
									$tmp_img = imagecreatetruecolor($new_width, $new_height);
									imagealphablending($tmp_img, false);
									imagesavealpha($tmp_img, true);
									$transparent = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
									imagefilledrectangle($tmp_img, 0, 0, $width, $height, $transparent);

									// copy and resize old image into new image
									imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

									// save thumbnail into a file
									imagepng($tmp_img, "{$pathToMedium}/{$destFileName}");
								} else {
									copy($destFullPath, "{$pathToMedium}/{$destFileName}");
								}
							}
							if (isset($property['maxWidth'])) {
								//Create a thumbnail if needed
								$thumbWidth = $property['maxWidth'];
								if ($width > $thumbWidth) {
									$new_width = $thumbWidth;
									$new_height = floor($height * ($thumbWidth / $width));

									if (isset($property['maxHeight'])) {
										$thumbHeight = $property['maxHeight'];
										if ($new_height > $thumbHeight) {
											$new_height = $thumbHeight;
											$new_width = floor($new_width * ($thumbHeight / $height));
										}
									}

									// create a new temporary image
									$tmp_img = imagecreatetruecolor($new_width, $new_height);
									imagealphablending($tmp_img, false);
									imagesavealpha($tmp_img, true);
									$transparent = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
									imagefilledrectangle($tmp_img, 0, 0, $width, $height, $transparent);

									// copy and resize old image into new image
									imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

									// save thumbnail into a file
									imagepng($tmp_img, "{$destFolder}/{$destFileName}");
								}
							}
						}
					}
					//store the actual filename
					$object->setProperty($propertyName, $destFileName, $property);
					$logger->log("Set $propertyName to $destFileName", Logger::LOG_DEBUG);
				}
			}

		}else if ($property['type'] == 'file'){
			//Make sure that the type is correct (jpg, png, or gif)
			if (isset($_REQUEST["remove{$propertyName}"])){
				$object->setProperty($propertyName, '', $property);
			}elseif (isset($_REQUEST["{$propertyName}_existing"]) && $_FILES[$propertyName]['error'] == 4){
				$object->setProperty($propertyName, $_REQUEST["{$propertyName}_existing"], $property);
			}else if (isset($_FILES[$propertyName])){
				if ($_FILES[$propertyName]["error"] > 0){
					//return an error to the browser
					$logger->log("Error uploading file " . $_FILES[$propertyName]["error"], Logger::LOG_ERROR);
				}else if (true){ //TODO: validate the file type
					if (array_key_exists('validTypes', $property)){
						$fileType = $_FILES[$propertyName]["type"];
						if (!in_array($fileType, $property['validTypes'])){
							AspenError::raiseError('Incorrect file type uploaded ' . $fileType);
						}
					}
					//Copy the full image to the correct location
					//Filename is the name of the object + the original filename
					$destFileName = $_FILES[$propertyName]["name"];
					$destFolder = $property['path'];
					if (!file_exists($destFolder)){
						if (!mkdir($destFolder, 0755, true)) {
							$logger->log("Could not create $destFolder", Logger::LOG_NOTICE);
						}
					}
					if (substr($destFolder, -1) == '/'){
						$destFolder = substr($destFolder, 0, -1);
					}

					$destFullPath = $destFolder . '/' . $destFileName;
					$copyResult = copy($_FILES[$propertyName]["tmp_name"], $destFullPath);
					if ($copyResult){
						$logger->log("Copied file from {$_FILES[$propertyName]["tmp_name"]} to $destFullPath", Logger::LOG_NOTICE);
					}else{
						$logger->log("Could not copy file from {$_FILES[$propertyName]["tmp_name"]} to $destFullPath", Logger::LOG_ERROR);
						if (!file_exists($_FILES[$propertyName]["tmp_name"])){
							$logger->log("  Uploaded file did not exist", Logger::LOG_ERROR);
						}
						if (!is_writable($destFullPath)){
							$logger->log("  Destination is not writable", Logger::LOG_ERROR);
						}
					}
					//store the actual filename
					$object->setProperty($propertyName, $destFullPath, $property);
				}
			}
		}else if ($property['type'] == 'uploaded_font'){
			//Make sure that the type is correct (jpg, png, or gif)
			if (isset($_REQUEST["remove{$propertyName}"])){
				$object->setProperty($propertyName, '', $property);
			}elseif (isset($_REQUEST["{$propertyName}_existing"]) && $_FILES[$propertyName]['error'] == 4){
				$object->setProperty($propertyName, $_REQUEST["{$propertyName}_existing"], $property);
			}else if (isset($_FILES[$propertyName])){
				if ($_FILES[$propertyName]["error"] > 0){
					//return an error to the browser
					$logger->log("Error uploading file " . $_FILES[$propertyName]["error"], Logger::LOG_ERROR);
				}else if (true){ //TODO: validate the file type
					//Copy the full image to the correct location
					//Filename is the name of the object + the original filename
					global $configArray;
					$destFileName = $_FILES[$propertyName]["name"];
					$destFolder = $configArray['Site']['local'] . '/fonts';
					$destFullPath = $destFolder . '/' . $destFileName;
					$copyResult = copy($_FILES[$propertyName]["tmp_name"], $destFullPath);
					if ($copyResult){
						$logger->log("Copied file from {$_FILES[$propertyName]["tmp_name"]} to $destFullPath", Logger::LOG_NOTICE);
					}else{
						$logger->log("Could not copy file from {$_FILES[$propertyName]["tmp_name"]} to $destFullPath", Logger::LOG_ERROR);
						if (!file_exists($_FILES[$propertyName]["tmp_name"])){
							$logger->log("  Uploaded file did not exist", Logger::LOG_ERROR);
						}
						if (!is_writable($destFullPath)){
							$logger->log("  Destination is not writable", Logger::LOG_ERROR);
						}
					}
					//store the actual filename
					$object->setProperty($propertyName, $destFileName, $property);
				}
			}
		}else if ($property['type'] == 'password'){
			if (strlen($_REQUEST[$propertyName]) > 0 && ($_REQUEST[$propertyName] == $_REQUEST[$propertyName . 'Repeat'])){
				$newValue = strip_tags(trim($_REQUEST[$propertyName]));
				if ($newValue != null) {
					$newValue = preg_replace('/\x{2029}/usm', '', $newValue);
				}
				$object->setProperty($propertyName, md5($newValue), $property);
			}
		}else if ($property['type'] == 'storedPassword'){
			if (strlen($_REQUEST[$propertyName]) > 0 && ($_REQUEST[$propertyName] == $_REQUEST[$propertyName . 'Repeat'])){
				$newValue = strip_tags(trim($_REQUEST[$propertyName]));
				if ($newValue != null) {
					$newValue = preg_replace('/\x{2029}/usm', '', $newValue);
				}
				$object->setProperty($propertyName, $newValue, $property);
			}
		}else if ($property['type'] == 'oneToMany'){
			//Check for deleted associations
			$deletions = isset($_REQUEST[$propertyName . 'Deleted']) ? $_REQUEST[$propertyName . 'Deleted'] : array();
			//Check for changes to the sort order
			if ($property['sortable'] == true && isset($_REQUEST[$propertyName . 'Weight'])){
				$weights = $_REQUEST[$propertyName . 'Weight'];
			}
			$values = array();
			if (isset($_REQUEST[$propertyName.'Id'])){
				$idsToSave = $_REQUEST[$propertyName.'Id'];
				$existingValues = $object->$propertyName;
				$subObjectType = $property['subObjectType'];  // the PHP Class name
				$subStructure = $property['structure'];
				foreach ($idsToSave as $key => $id){
					//Create the subObject
					if ($id < 0 || $id == ""){
						$subObject = new $subObjectType();
						$id = $key;
					} else {
						if (!isset($existingValues[$id])) {
							if (!isset($deletions[$id]) || ($deletions[$id] == 'false')) {
								$logger->log("$subObjectType $id has been deleted from the database, but is still present in the interface", Logger::LOG_ERROR);
							}
							continue;
						} else {
							$subObject = $existingValues[$id];
						}
					}

					$deleted = isset($deletions[$id]) ? $deletions[$id] : false;
					if ($deleted == 'true'){
						$subObject->deleteOnSave = true;
					}else{
						//Update properties of each associated object
						foreach ($subStructure as $subProperty){
							$requestKey = $propertyName . '_' . $subProperty['property'];
							$subPropertyName = $subProperty['property'];
							if (in_array($subProperty['type'], array('text', 'enum', 'integer', 'numeric', 'textarea', 'html', 'markdown','javascript', 'multiSelect') )){
								$subObject->setProperty($subPropertyName, $_REQUEST[$requestKey][$id], $subProperty);
							}elseif (in_array($subProperty['type'], array('checkbox') )){
								$subObject->setProperty($subPropertyName, isset($_REQUEST[$requestKey][$id]) ? 1 : 0, $subProperty);
							}elseif ($subProperty['type'] == 'date'){
								if (strlen($_REQUEST[$requestKey][$id]) == 0 || $_REQUEST[$requestKey][$id] == '0000-00-00'){
									$subObject->setProperty($subPropertyName, null, $subProperty);
								}else{
									$dateParts = date_parse($_REQUEST[$requestKey][$id]);
									$time = $dateParts['year'] . '-' . $dateParts['month'] . '-' . $dateParts['day'];
									$subObject->setProperty($subPropertyName, $time, $subProperty);
								}
							}elseif (!in_array($subProperty['type'], array('label', 'foreignKey', 'oneToMany') )){
								//echo("Invalid Property Type " . $subProperty['type']);
								$logger->log("Invalid Property Type " . $subProperty['type'], Logger::LOG_DEBUG);
							}
						}
					}
					if ($property['sortable'] == true && isset($weights)){
						$subObject->setProperty('weight', $weights[$id], null);
					}

					//Update the values array
					$values[$id] = $subObject;
				}
			}

			$object->$propertyName = $values;
		}
	}

	static function getObjectListFilters($objectStructure){

	}

	static function getObjectList($objectStructure, $objectsToShow){

	}

	static function getObjectExportFile($objectStructure, $objectsToExport, $exportFilename){

	}
	static function compareObjects($objectStructure, $object1, $object2){

	}
	static function importObjectsFromFile($objectStructure, $objectType, $importFilename){

	}

	static function getFileUploadMessage($errorNo, $fieldname){
		$errorMessages = array(
		0=>"There is no error, the file for $fieldname uploaded with success",
		1=>"The uploaded file for $fieldname exceeds the maximum file size for the server",
		2=>"The uploaded file for $fieldname exceeds the maximum file size for this field",
		3=>"The uploaded file for $fieldname was only partially uploaded",
		4=>"No file was uploaded for $fieldname",
		6=>"Missing a temporary folder for $fieldname"
		);
		return $errorMessages[$errorNo];
	}

}