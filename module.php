<?php

class FilesModule extends AApiModule
{
	public $oApiFilesManager = null;
	
	protected $oMinModuleDecorator = null;
	
	protected $aSettingsMap = array(
		'EnableUploadSizeLimit' => array(true, 'bool'),
		'UploadSizeLimitMb' => array(5, 'int'),
		'Disabled' => array(false, 'bool'),
		'EnableCorporate' => array(true, 'bool'),
		'UserSpaceLimitMb' => array(100, 'int'),
		'CustomTabTitle' => array('', 'string'),
		'MaxFileSizeForMakingThumbnail' => array(5242880, 'int'), // 5MB
	);

	public function init() 
	{
		$this->incClass('item');
		$this->oApiFilesManager = $this->GetManager('', 'sabredav');
		
		$this->AddEntry('files-pub', 'EntryFilesPub');
	}
	
	/**
	 * Returns module settings for specified user.
	 * 
	 * @param \CUser $oUser User settings are obtained for.
	 * 
	 * @return array
	 */
	public function GetAppData($oUser = null)
	{
		return array(
			'EnableModule' => true, // AppData.User.FilesEnable
			'PublicHash' => '', // AppData.FileStoragePubHash
			'PublicName' => '', // AppData.FileStoragePubParams.Name
			'EnableUploadSizeLimit' => $this->getConfig('EnableUploadSizeLimit', false),
			'UploadSizeLimitMb' => $this->getConfig('EnableUploadSizeLimit', false) ? $this->getConfig('UploadSizeLimitMb', 0) : 0,
			'EnableCorporate' => $this->getConfig('EnableCorporate', false),
			'UserSpaceLimitMb' => $this->getConfig('UserSpaceLimitMb', 0),
			'CustomTabTitle' => $this->getConfig('CustomTabTitle', ''),
		);
	}
	
	/**
	 * @api {post} ?/Api/ UpdateSettings
	 * @apiName UpdateSettings
	 * @apiGroup Files
	 * @apiDescription Updates module's settings - saves them to config.json file.
	 * 
	 * @apiParam {string=Files} Module Module name
	 * @apiParam {string=UpdateSettings} Method Method name
	 * @apiParam {string} AuthToken Auth token
	 * 
	 * @apiParam {boolean} EnableUploadSizeLimit Enable file upload size limit setting.
	 * @apiParam {int} UploadSizeLimitMb Upload file size limit setting in Mb.
	 * @apiParam {boolean} EnableCorporate Enable corporate storage in Files.
	 * @apiParam {int} UserSpaceLimitMb User space limit setting in Mb.
	 * 
	 * @apiParamExample {json} Request-Example:
{
	Module: 'Files',
	Method: 'UpdateSettings',
	AuthToken: 'token_value',
	EnableUploadSizeLimit: true,
	UploadSizeLimitMb: 5,
	EnableCorporate: true,
	UserSpaceLimitMb: 10,
}
	 * 
	 * @apiSuccess {string} Module Module name
	 * @apiSuccess {string} Method Method name
	 * @apiSuccess {bool} Result Indicates if request execution was successfull
	 * @apiSuccess {int} ErrorCode Error code
	 * @apiSuccess {float} Time Request execution time on the server
	 * 
	 * @apiSuccessExample {json} Success response example:
{
	Module: 'Files',
	Method: 'UpdateSettings',
	Result: true,
	'@Time': 0.31426095962524
}
	 * 
	 * @apiSuccessExample {json} Error response example:
{
	Module: 'Files',
	Method: 'UpdateSettings',
	Result: false,
	ErrorCode: 102,
	'@Time': 0.31426095962524
}
	 */
	/**
	 * Updates module's settings - saves them to config.json file.
	 * 
	 * @param boolean $EnableUploadSizeLimit Enable file upload size limit setting.
	 * @param int $UploadSizeLimitMb Upload file size limit setting in Mb.
	 * @param boolean $EnableCorporate Enable corporate storage in Files.
	 * @param int $UserSpaceLimitMb User space limit setting in Mb.
	 */
	public function UpdateSettings($EnableUploadSizeLimit, $UploadSizeLimitMb, $EnableCorporate, $UserSpaceLimitMb)
	{
		$this->setConfig('EnableUploadSizeLimit', $EnableUploadSizeLimit);
		$this->setConfig('UploadSizeLimitMb', $UploadSizeLimitMb);
		$this->setConfig('EnableCorporate', $EnableCorporate);
		$this->setConfig('UserSpaceLimitMb', $UserSpaceLimitMb);
		$this->saveModuleConfig();
		return true;
	}
	
	/**
	* Returns Min module decorator.
	* 
	* @return \CApiModuleDecorator
	*/
	private function getMinModuleDecorator()
	{
		if ($this->oMinModuleDecorator === null)
		{
			$this->oMinModuleDecorator = \CApi::GetModuleDecorator('Min');
		}
		
		return $this->oMinModuleDecorator;
	}
	
	/**
	 * Downloads file, views file or makes thumbnail for file.
	 * 
	 * @param string $sType Storage type - personal, corporate.
	 * @param string $sPath Path to folder contained file.
	 * @param string $sFileName File name.
	 * @param string $sAuthToken Authorization token.
	 * @param boolean $bDownload Indicates if file should be downloaded or viewed.
	 * @param boolean $bThumbnail Indicates if thumbnail should be created for file.
	 * 
	 * @return boolean
	 */
	private function getRawFile($sType, $sPath, $sFileName, $sAuthToken, $bDownload = true, $bThumbnail = false)
	{
		if ($bThumbnail)
		{
//			\CApiResponseManager::verifyCacheByKey($sRawKey);
		}

		$sHash = ""; // TODO: 
		$oModuleDecorator = $this->getMinModuleDecorator();
		$mMin = ($oModuleDecorator) ? $oModuleDecorator->GetMinByHash($sHash) : array();
		
		$iUserId = (!empty($mMin['__hash__'])) ? $mMin['UserId'] : \CApi::getLogginedUserId($sAuthToken);

		$oTenant = null;

/*		$oApiTenants = \CApi::GetCoreManager('tenants');
		if ($iUserId && $oApiTenants) {
			$oTenant = (0 < $iUserId->IdTenant) ? $oApiTenants->getTenantById($iUserId->IdTenant) :
				$oApiTenants->getDefaultGlobalTenant();
		}
 * 
 */
		
		if ($this->oApiCapabilityManager->isFilesSupported($iUserId) && 
				/*$oTenant &&*/ isset($sType, $sPath, $sFileName)) {
			
			$mResult = false;
			
			$sContentType = (empty($sFileName)) ? 'text/plain' : \MailSo\Base\Utils::MimeContentType($sFileName);
			
			$oFileInfo = $this->oApiFilesManager->getFileInfo($iUserId, $sType, $sPath, $sFileName);
			
			
			if ($oFileInfo && $oFileInfo->IsLink) 
			{
				
				$iLinkType = \api_Utils::GetLinkType($oFileInfo->LinkUrl);

				if (isset($iLinkType)) {
					
					if (\EFileStorageLinkType::GoogleDrive === $iLinkType) {
						
						$oSocial = $oTenant->getSocialByName('google');
						if ($oSocial) {
							
							$oInfo = \api_Utils::GetGoogleDriveFileInfo($oFileInfo->LinkUrl, $oSocial->SocialApiKey);
							$sFileName = isset($oInfo->title) ? $oInfo->title : $sFileName;
							$sContentType = \MailSo\Base\Utils::MimeContentType($sFileName);

							if (isset($oInfo->downloadUrl)) {
								
								$mResult = \MailSo\Base\ResourceRegistry::CreateMemoryResource();
								$this->oHttp->SaveUrlToFile($oInfo->downloadUrl, $mResult); // todo
								rewind($mResult);
							}
						}
					} else/* if (\EFileStorageLinkType::DropBox === (int)$aFileInfo['LinkType'])*/ {
						
						if (\EFileStorageLinkType::DropBox === $iLinkType) {
							
							$oFileInfo->LinkUrl = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $oFileInfo->LinkUrl);
						}
						$mResult = \MailSo\Base\ResourceRegistry::CreateMemoryResource();
						$sFileName = basename($oFileInfo->LinkUrl);
						$sContentType = \MailSo\Base\Utils::MimeContentType($sFileName);
						
						$this->oHttp->SaveUrlToFile($oFileInfo->LinkUrl, $mResult); // todo
						rewind($mResult);
					}
				}
			} else {
				
				$mResult = $this->oApiFilesManager->getFile($iUserId, $sType, $sPath, $sFileName);
			}
			if (false !== $mResult) {
				
				if (is_resource($mResult)) {
					
//					$sFileName = $this->clearFileName($oFileInfo->Name, $sContentType); // todo
					$sContentType = \MailSo\Base\Utils::MimeContentType($sFileName);
					\CApiResponseManager::OutputHeaders($bDownload, $sContentType, $sFileName);
			
					if ($bThumbnail) {
						
//						$this->cacheByKey($sRawKey);	// todo
						\CApiResponseManager::GetThumbResource($iUserId, $mResult, $sFileName);
					} else if ($sContentType === 'text/html') {
						
						echo(\MailSo\Base\HtmlUtils::ClearHtmlSimple(stream_get_contents($mResult)));
					} else {
						
						\MailSo\Base\Utils::FpassthruWithTimeLimitReset($mResult);
					}
					
					@fclose($mResult);
				}

				return true;
			}
		}

		return false;		
	}
	
	/**
	 * Uploads file from client side.
	 * 
	 * @param string $Type Type of storage - personal, corporate.
	 * @param string $Path Path to folder than should contain uploaded file.
	 * @param array $FileData Uploaded file information. Contains fields size, name, tmp_name.
	 * @param string $AuthToken Authentication token.
	 * 
	 * @return array Array with fields Name, TempName, MimeType, Size, Hash.
	 *		Name string Original file name
	 *		TempName string Temporary file name
	 *		MimeType string Mime type of file
	 *		Size int File size
	 *		Hash string Hash used for file download, file view or getting file thumbnail
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function UploadFile($Type, $Path, $FileData, $AuthToken)
	{
		$iUserId = \CApi::getLogginedUserId($AuthToken);
		$oApiFileCacheManager = \CApi::GetSystemManager('filecache');

		$sError = '';
		$aResponse = array();

		if ($iUserId)
		{
			if (is_array($FileData))
			{
				$iSize = (int) $FileData['size'];
				if ($Type === \EFileStorageTypeStr::Personal)
				{
					$aQuota = $this->getQuota($iUserId);
					if ($aQuota['Limit'] > 0 && $aQuota['Used'] + $iSize > $aQuota['Limit'])
					{
						throw new \System\Exceptions\ClientException(\System\Notifications::CanNotUploadFileQuota);
					}
				}
				
				$sUploadName = $FileData['name'];
				$sMimeType = \MailSo\Base\Utils::MimeContentType($sUploadName);

				$sSavedName = 'upload-post-'.md5($FileData['name'].$FileData['tmp_name']);
				$rData = false;
				if (is_resource($FileData['tmp_name']))
				{
					$rData = $FileData['tmp_name'];
				}
				else if ($oApiFileCacheManager->moveUploadedFile($iUserId, $sSavedName, $FileData['tmp_name']))
				{
					$rData = $oApiFileCacheManager->getFile($iUserId, $sSavedName);
				}
				if ($rData)
				{
					$this->oApiFilesManager->createFile($iUserId, $Type, $Path, $sUploadName, $rData, false);

					$aResponse['File'] = array(
						'Name' => $sUploadName,
						'TempName' => $sSavedName,
						'MimeType' => $sMimeType,
						'Size' =>  (int) $iSize,
						'Hash' => \CApi::EncodeKeyValues(array(
							'TempFile' => true,
							'UserID' => $iUserId,
							'Name' => $sUploadName,
							'TempName' => $sSavedName
						))
					);
				}
			}
		}
		else
		{
			$sError = 'auth';
		}

		if (0 < strlen($sError))
		{
			$aResponse['Error'] = $sError;
		}
		
		return $aResponse;
	}
	
	/**
	 * Downloads file.
	 * 
	 * @param string $Type Storage type - personal, corporate.
	 * @param string $Path Path to folder contained file.
	 * @param string $FileName File name.
	 * @param string $AuthToken Authorization token.
	 * 
	 * @return boolean
	 */
	public function DownloadFile($Type, $Path, $FileName, $AuthToken)
	{
		return $this->getRawFile($Type, $Path, $FileName, $AuthToken, true);
	}
	
	/**
	 * Views file.
	 * 
	 * @param string $Type Storage type - personal, corporate.
	 * @param string $Path Path to folder contained file.
	 * @param string $FileName File name.
	 * @param string $AuthToken Authorization token.
	 * 
	 * @return boolean
	 */
	public function ViewFile($Type, $Path, $FileName, $AuthToken)
	{
		return $this->getRawFile($Type, $Path, $FileName, $AuthToken, false);
	}

	/**
	 * Makes thumbnail for file.
	 * 
	 * @param string $Type Storage type - personal, corporate.
	 * @param string $Path Path to folder contained file.
	 * @param string $FileName File name.
	 * @param string $AuthToken Authorization token.
	 * 
	 * @return boolean
	 */
	public function GetFileThumbnail($Type, $Path, $FileName, $AuthToken)
	{
		return $this->getRawFile($Type, $Path, $FileName, $AuthToken, false, true);
	}

	/**
	 * Returns storages avaliable for logged in user.
	 * 
	 * @return array Array of items with fields Type, DisplayName, IsExternal
	 *			Type string Storage type - personal, corporate
	 *			DisplayName string Storage display name
	 *			IsExternal bool Indicates if storage external or not
	 */
	public function GetStorages()
	{
		$iUserId = \CApi::getLogginedUserId();
		$aStorages = [
			[
				'Type' => 'personal', 
				'DisplayName' => $this->i18N('LABEL_PERSONAL_STORAGE', $iUserId), 
				'IsExternal' => false
			]
		];
		if ($this->getConfig('EnableCorporate', false))
		{
			$aStorages[] = [
				'Type' => 'corporate', 
				'DisplayName' => $this->i18N('LABEL_CORPORATE_STORAGE', $iUserId), 
				'IsExternal' => false
			];
		}
		return $aStorages;
	}	
	
	/**
	 * @ignore
	 * @return array
	 */
	public function GetExternalStorages()
	{
		$oResult = array();
		return $oResult; // TODO
//		$iUserId = \CApi::getLogginedUserId();
//		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
//		{
//			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
//		}
//		
//		\CApi::Plugin()->RunHook('filestorage.get-external-storages', array($iUserId, &$oResult));
//
//		return $oResult;
	}

	/**
	 * Returns used space and space limit for specified user.
	 * 
	 * @param int $iUserId User identifier.
	 * 
	 * @return array Array of items with fields Used, Limit
	 *			Used int Amount of space used by user
	 *			Limit int Limit of space for user
	 */
	private function getQuota($iUserId)
	{
		return array(
			'Used' => $this->oApiFilesManager->getUserUsedSpace($iUserId, [\EFileStorageTypeStr::Personal]),
			'Limit' => $this->getConfig('UserSpaceLimitMb', 0) * 1024 * 1024
		);
	}

	/**
	 * Returns file list and user quota information.
	 * 
	 * @param string $Type Type of storage.
	 * @param string $Path Path to folder files are obtained from.
	 * @param string $Pattern
	 * 
	 * @return array Array of items with fields Items, Quota
	 *			Items array Array of files objects
	 *			Quota array Array of items with fields Used, Limit
	 *					Used int Amount of space used by user
	 *					Limit int Limit of space for user
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function GetFiles($Type, $Path, $Pattern)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}
		
		$aUsers = array();
		$aFiles = $this->oApiFilesManager->getFiles($iUserId, $Type, $Path, $Pattern);
		foreach ($aFiles as $oFile)
		{
			if (!isset($aUsers[$oFile->Owner]))
			{
				$oUser = \CApi::GetModuleDecorator('Core')->GetUser($oFile->Owner);
				$aUsers[$oFile->Owner] = $oUser ? $oUser->Name : '';
			}
			$oFile->Owner = $aUsers[$oFile->Owner];
		}
		
		return array(
			'Items' => $aFiles,
			'Quota' => $this->getQuota($iUserId)
		);
	}

	/**
	 * Returns list of public files.
	 * 
	 * @param string $Hash Hash to identify the list of files to return. Containes information about user identificator, type of storage, path to public folder, name of public folder.
	 * @param string $Path Path to folder contained files to return.
	 * 
	 * @return array Array of items with fields Items, Quota
	 *			Items array Array of files objects
	 *			Quota array Array of items with fields Used, Limit
	 *					Used int Amount of space used by user
	 *					Limit int Limit of space for user
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function GetPublicFiles($Hash, $Path)
	{
		$iUserId = null;
		$oResult = array();

		$mMin = \CApi::ExecuteMethod('Min::GetMinByHash', array('Hash' => $Hash));
		if (!empty($mMin['__hash__']))
		{
			$iUserId = $mMin['UserId'];
			if ($iUserId)
			{
				if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
				{
					throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
				}
				$Path =  implode('/', array($mMin['Path'], $mMin['Name'])) . $Path;

				$oResult['Items'] = $this->oApiFilesManager->getFiles($iUserId, $mMin['Type'], $Path);
				$oResult['Quota'] = $this->getQuota($iUserId);
			}
		}

		return $oResult;
	}	

	/**
	 * Creates folder.
	 * 
	 * @param string $Type Type of storage - personal, corporate.
	 * @param string $Path Path to new folder.
	 * @param string $FolderName New folder name.
	 * 
	 * @return boolean
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function CreateFolder($Type, $Path, $FolderName)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId)) {
			
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}

		return $this->oApiFilesManager->createFolder($iUserId, $Type, $Path, $FolderName);
	}
	
	/**
	 * Creates link.
	 * 
	 * @param string $Type Type of storage - personal, corporate.
	 * @param string $Path Path to new link.
	 * @param string $Link Link value.
	 * @param string $Name Link name.
	 * 
	 * @return boolean
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function CreateLink($Type, $Path, $Link, $Name)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}

		return $this->oApiFilesManager->createLink($iUserId, $Type, $Path, $Link, $Name);
	}
	
	/**
	 * Deletes files and folder specified with list.
	 * 
	 * @param string $Type Type of storage - personal, corporate.
	 * @param array $Items Array of items to delete.
	 * 
	 * @return boolean
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function Delete($Type, $Items)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}

		$bResult = false;
		
		foreach ($Items as $oItem)
		{
			$bResult = $this->oApiFilesManager->delete($iUserId, $Type, $oItem['Path'], $oItem['Name']);
		}
		
		return $bResult;
	}	

	/**
	 * Renames folder, file or link.
	 * 
	 * @param string $Type Type of storage - personal, corporate.
	 * @param string $Path Path to item to rename.
	 * @param string $Name Current name of the item.
	 * @param string $NewName New name of the item.
	 * @param boolean $IsLink Indicates if the item is link or not.
	 * 
	 * @return boolean
	 * 
	 * @throws \System\Exceptions\ClientException
	 */
	public function Rename($Type, $Path, $Name, $NewName, $IsLink)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}
		
		$sClearedNewName = \trim(\MailSo\Base\Utils::ClearFileName($NewName));
		$sCorrectedNewName = $this->oApiFilesManager->getNonExistingFileName($iUserId, $Type, $Path, $sClearedNewName);
		
		return $this->oApiFilesManager->rename($iUserId, $Type, $Path, $Name, $sCorrectedNewName, $IsLink);
	}	

	public function Copy($FromType, $ToType, $FromPath, $ToPath, $Files)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}

		$oResult = null;
		
		foreach ($Files as $aItem)
		{
			$bFolderIntoItself = $aItem['IsFolder'] && $ToPath === $FromPath.'/'.$aItem['Name'];
			if (!$bFolderIntoItself)
			{
				$sNewName = $this->oApiFilesManager->getNonExistingFileName($iUserId, $ToType, $ToPath, $aItem['Name']);
				$oResult = $this->oApiFilesManager->copy($iUserId, $FromType, $ToType, $FromPath, $ToPath, $aItem['Name'], $NewName);
			}
		}
		return $oResult;
	}	

	public function Move($FromType, $ToType, $FromPath, $ToPath, $Files)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}
		$oResult = null;
		
		foreach ($Files as $aItem)
		{
			$bFolderIntoItself = $aItem['IsFolder'] && $ToPath === $FromPath.'/'.$aItem['Name'];
			if (!$bFolderIntoItself)
			{
				$NewName = $this->oApiFilesManager->getNonExistingFileName($iUserId, $ToType, $ToPath, $aItem['Name']);
				$oResult = $this->oApiFilesManager->move($iUserId, $FromType, $ToType, $FromPath, $ToPath, $aItem['Name'], $NewName);
			}
		}
		return $oResult;
	}	
	
	public function CreatePublicLink($Type, $Path, $Name, $Size, $IsFolder)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}
		$IsFolder = $IsFolder === '1' ? true : false;
		
		return $this->oApiFilesManager->createPublicLink($iUserId, $Type, $Path, $Name, $Size, $IsFolder);
	}	
	
	public function DeletePublicLink($Type, $Path, $Name)
	{
		$iUserId = \CApi::getLogginedUserId();
		if (!$this->oApiCapabilityManager->isFilesSupported($iUserId))
		{
			throw new \System\Exceptions\ClientException(\System\Notifications::FilesNotAllowed);
		}
		
		return $this->oApiFilesManager->deletePublicLink($iUserId, $Type, $Path, $Name);
	}
	
	/**
	 * @return array
	 */
	public function CheckUrl()
	{
		$iUserId = \CApi::getLogginedUserId();
		$mResult = false;

		if ($iUserId)
		{
			$sUrl = trim($this->getParamValue('Url', ''));

			if (!empty($sUrl))
			{
				$iLinkType = \api_Utils::GetLinkType($sUrl);
				if ($iLinkType === \EFileStorageLinkType::GoogleDrive)
				{
					$oApiTenants = \CApi::GetSystemManager('tenants');
					if ($oApiTenants)
					{
						$oTenant = (0 < $iUserId->IdTenant) ? $oApiTenants->getTenantById($iUserId->IdTenant) :
							$oApiTenants->getDefaultGlobalTenant();
					}
					$oSocial = $oTenant->getSocialByName('google');
					if ($oSocial)
					{
						$oInfo = \api_Utils::GetGoogleDriveFileInfo($sUrl, $oSocial->SocialApiKey);
						if ($oInfo)
						{
							$mResult['Size'] = 0;
							if (isset($oInfo->fileSize))
							{
								$mResult['Size'] = $oInfo->fileSize;
							}
							else
							{
								$aRemoteFileInfo = \api_Utils::GetRemoteFileInfo($sUrl);
								$mResult['Size'] = $aRemoteFileInfo['size'];
							}
							$mResult['Name'] = isset($oInfo->title) ? $oInfo->title : '';
							$mResult['Thumb'] = isset($oInfo->thumbnailLink) ? $oInfo->thumbnailLink : null;
						}
					}
				}
				else
				{
					//$sUrl = \api_Utils::GetRemoteFileRealUrl($sUrl);
					$oInfo = \api_Utils::GetOembedFileInfo($sUrl);
					if ($oInfo)
					{
						$mResult['Size'] = isset($oInfo->fileSize) ? $oInfo->fileSize : '';
						$mResult['Name'] = isset($oInfo->title) ? $oInfo->title : '';
						$mResult['LinkType'] = $iLinkType;
						$mResult['Thumb'] = isset($oInfo->thumbnail_url) ? $oInfo->thumbnail_url : null;
					}
					else
					{
						if (\api_Utils::GetLinkType($sUrl) === \EFileStorageLinkType::DropBox)
						{
							$sUrl = str_replace('?dl=0', '', $sUrl);
						}

						$sUrl = \api_Utils::GetRemoteFileRealUrl($sUrl);
						if ($sUrl)
						{
							$aRemoteFileInfo = \api_Utils::GetRemoteFileInfo($sUrl);
							$sFileName = basename($sUrl);
							$sFileExtension = \api_Utils::GetFileExtension($sFileName);

							if (empty($sFileExtension))
							{
								$sFileExtension = \api_Utils::GetFileExtensionFromMimeContentType($aRemoteFileInfo['content-type']);
								$sFileName .= '.'.$sFileExtension;
							}

							if ($sFileExtension === 'htm')
							{
								$oCurl = curl_init();
								\curl_setopt_array($oCurl, array(
									CURLOPT_URL => $sUrl,
									CURLOPT_FOLLOWLOCATION => true,
									CURLOPT_ENCODING => '',
									CURLOPT_RETURNTRANSFER => true,
									CURLOPT_AUTOREFERER => true,
									CURLOPT_SSL_VERIFYPEER => false, //required for https urls
									CURLOPT_CONNECTTIMEOUT => 5,
									CURLOPT_TIMEOUT => 5,
									CURLOPT_MAXREDIRS => 5
								));
								$sContent = curl_exec($oCurl);
								//$aInfo = curl_getinfo($oCurl);
								curl_close($oCurl);

								preg_match('/<title>(.*?)<\/title>/s', $sContent, $aTitle);
								$sTitle = isset($aTitle['1']) ? trim($aTitle['1']) : '';
							}

							$mResult['Name'] = isset($sTitle) && strlen($sTitle)> 0 ? $sTitle : urldecode($sFileName);
							$mResult['Size'] = $aRemoteFileInfo['size'];
						}
					}
				}
			}
		}
		
		return $mResult;
	}	
	
	public function EntryFilesPub()
	{
		$sResult = '';
		
		$sFilesPub = \MailSo\Base\Http::NewInstance()->GetQuery('files-pub');
		$mData = \CApi::ExecuteMethod('Min::GetMinByHash', array('Hash' => $sFilesPub));
		
		if (is_array($mData) && isset($mData['IsFolder']) && $mData['IsFolder'])
		{
			$oApiIntegrator = \CApi::GetSystemManager('integrator');

			if ($oApiIntegrator)
			{
				$oCoreClientModule = \CApi::GetModule('CoreClient');
				if ($oCoreClientModule instanceof \AApiModule) {
					$sResult = file_get_contents($oCoreClientModule->GetPath().'/templates/Index.html');
					if (is_string($sResult)) {
						$sFrameOptions = \CApi::GetConf('labs.x-frame-options', '');
						if (0 < \strlen($sFrameOptions)) {
							@\header('X-Frame-Options: '.$sFrameOptions);
						}

						$sAuthToken = isset($_COOKIE[\System\Service::AUTH_TOKEN_KEY]) ? $_COOKIE[\System\Service::AUTH_TOKEN_KEY] : '';
						$sResult = strtr($sResult, array(
							'{{AppVersion}}' => PSEVEN_APP_VERSION,
							'{{IntegratorDir}}' => $oApiIntegrator->isRtl() ? 'rtl' : 'ltr',
							'{{IntegratorLinks}}' => $oApiIntegrator->buildHeadersLink(),
							'{{IntegratorBody}}' => $oApiIntegrator->buildBody('-files-pub')
						));
					}
				}
			}
		}
		else if ($mData && isset($mData['__hash__'], $mData['Name'], $mData['Size']))
		{
			$sUrl = (bool) \CApi::GetConf('labs.server-use-url-rewrite', false) ? '/download/' : '?/Min/Download/';

			$sUrlRewriteBase = (string) \CApi::GetConf('labs.server-url-rewrite-base', '');
			if (!empty($sUrlRewriteBase))
			{
				$sUrlRewriteBase = '<base href="'.$sUrlRewriteBase.'" />';
			}

			$sResult = file_get_contents($this->GetPath().'/templates/FilesPub.html');
			if (is_string($sResult))
			{
				$sResult = strtr($sResult, array(
					'{{Url}}' => $sUrl.$mData['__hash__'], 
					'{{FileName}}' => $mData['Name'],
					'{{FileSize}}' => \api_Utils::GetFriendlySize($mData['Size']),
					'{{FileType}}' => \api_Utils::GetFileExtension($mData['Name']),
					'{{BaseUrl}}' => $sUrlRewriteBase 
				));
			}
			else
			{
				\CApi::Log('Empty template.', \ELogLevel::Error);
			}
		}

		return $sResult;
	}
	
	/**
	 * @return array
	 */
	public function MinShare()
	{
		$mData = $this->getParamValue('Result', false);

		if ($mData && isset($mData['__hash__'], $mData['Name'], $mData['Size']))
		{
			$bUseUrlRewrite = (bool) \CApi::GetConf('labs.server-use-url-rewrite', false);			
			$sUrl = '?/Min/Download/';
			if ($bUseUrlRewrite)
			{
				$sUrl = '/download/';
			}
			
			$sUrlRewriteBase = (string) \CApi::GetConf('labs.server-url-rewrite-base', '');
			if (!empty($sUrlRewriteBase))
			{
				$sUrlRewriteBase = '<base href="'.$sUrlRewriteBase.'" />';
			}
		
			return array(
				'Template' => 'templates/FilesPub.html',
				'{{Url}}' => $sUrl.$mData['__hash__'], 
				'{{FileName}}' => $mData['Name'],
				'{{FileSize}}' => \api_Utils::GetFriendlySize($mData['Size']),
				'{{FileType}}' => \api_Utils::GetFileExtension($mData['Name']),
				'{{BaseUrl}}' => $sUrlRewriteBase 
			);
		}
		return false;
	}	

}
