<?php

/* -AFTERLOGIC LICENSE HEADER- */

/**
 * CApiFilestorageManager class summary
 * 
 * @package Filestorage
 */
class CApiFilesManager extends AApiManagerWithStorage
{
	/**
	 * @param CApiGlobalManager &$oManager
	 */
	public function __construct(CApiGlobalManager &$oManager, $sForcedStorage = '', AApiModule $oModule = null)
	{
		parent::__construct('', $oManager, $sForcedStorage, $oModule);
	}
	
	/**
	 * Checks if file exists. 
	 * 
	 * @param CAccount $oAccount Account object. 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sName Filename. 
	 * 
	 * @return bool
	 */
	public function isFileExists($oAccount, $iType, $sPath, $sName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.file-exists', array($oAccount, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->isFileExists($oAccount, $iType, $sPath, $sName);
		}
		return $bResult;
	}

	/**
	 * Allows for reading contents of the shared file. [Aurora only.](http://dev.afterlogic.com/aurora)
	 * 
	 * @param CAccount $oAccount
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sName Filename. 
	 * 
	 * @return resource|bool
	 */
	public function getSharedFile($oAccount, $iType, $sPath, $sName)
	{
		return $this->oStorage->getSharedFile($oAccount, $iType, $sPath, $sName);
	}

	/**
	 * Retrieves array of metadata on the specific file. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder.
	 * @param string $sName Filename. 
	 * 
	 * @return CFileStorageItem
	 */
	public function getFileInfo($oAccount, $iType, $sPath, $sName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.get-file-info', array($oAccount, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->getFileInfo($oAccount, $iType, $sPath, $sName);
		}
		return $bResult;
	}

	/**
	 * Retrieves array of metadata on the specific directory. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder. 
	 * 
	 * @return CFileStorageItem
	 */
	public function getDirectoryInfo($oAccount, $iType, $sPath)
	{
		return $this->oStorage->getDirectoryInfo($oAccount, $iType, $sPath);
	}

	/**
	 * Allows for reading contents of the file. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sName Filename. 
	 * 
	 * @return resource|bool
	 */
	public function getFile($oAccount, $iType, $sPath, $sName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.get-file', array($oAccount, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->getFile($oAccount, $iType, $sPath, $sName);
		}
		return $bResult;
	}

	/**
	 * Creates public link for specific file or folder. 
	 * 
	 * @param int $iUserId
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder. 
	 * @param string $sName Filename. 
	 * @param string $sSize Size information, it will be displayed when recipient opens the link. 
	 * @param string $bIsFolder If **true**, it is assumed the link is created for a folder, **false** otherwise. 
	 * 
	 * @return string|bool
	 */
	public function createPublicLink($iUserId, $iType, $sPath, $sName, $sSize, $bIsFolder)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.create-public-link', array($iUserId, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->createPublicLink($iUserId, $iType, $sPath, $sName, $sSize, $bIsFolder);
		}
		return $bResult;
	}
	
	/**
	 * Removes public link created for specific file or folder. 
	 * 
	 * @param int $iUserId
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder. 
	 * @param string $sName Filename. 
	 * 
	 * @return bool
	 */
	public function deletePublicLink($iUserId, $iType, $sPath, $sName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.delete-public-link', array($iUserId, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->deletePublicLink($iUserId, $iType, $sPath, $sName);
		}
		return $bResult;
	}

	/**
	 * Performs search for files. 
	 * 
	 * @param int $iUserId 
	 * @param string $sType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder. 
	 * @param string $sPattern Search string. 
	 * 
	 * @return array  array of \CFileStorageItem. 
	 */
	public function getFiles($iUserId, $sType, $sPath, $sPattern = '')
	{
		$bResult = false;
		$bBreak = false;
//		\CApi::Plugin()->RunHook('filestorage.get-files', array($oAccount, $sType, $sPath, $sPattern, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->getFiles($iUserId, $sType, $sPath, $sPattern);
		}
		return $bResult;
	}

	/**
	 * Creates a new folder. 
	 * 
	 * @param int $iUserId
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the parent folder, empty string means top-level folder is created. 
	 * @param string $sFolderName Folder name. 
	 * 
	 * @return bool
	 */
	public function createFolder($iUserId, $iType, $sPath, $sFolderName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.create-folder', array($iUserId, $iType, $sPath, $sFolderName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->createFolder($iUserId, $iType, $sPath, $sFolderName);
		}
		return $bResult;
	}
	
	/**
	 * Creates a new file. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is created in the root folder. 
	 * @param string $sFileName Filename. 
	 * @param $mData Data to be stored in the file. 
	 * @param bool $bOverride If **true**, existing file with that name will be overwritten. 
	 * 
	 * @return bool
	 */
	public function createFile($oAccount, $iType, $sPath, $sFileName, $mData, $bOverride = true)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.create-file', array($oAccount, $iType, $sPath, $sFileName, $mData, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			if (!$bOverride)
			{
				$sFileName = $this->oStorage->getNonExistingFileName($oAccount, $iType, $sPath, $sFileName);
			}
			$bResult = $this->oStorage->createFile($oAccount, $iType, $sPath, $sFileName, $mData);
		}
		return $bResult;
	}
	
	/**
	 * Creates a link to arbitrary online content. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the link. 
	 * @param string $sLink URL of the item to be linked. 
	 * @param string $sName Name of the link. 
	 * 
	 * @return bool
	 */
	public function createLink($oAccount, $iType, $sPath, $sLink, $sName)
	{
		return $this->oStorage->createLink($oAccount, $iType, $sPath, $sLink, $sName);
	}	
	
	/**
	 * Removes file or folder. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sName Filename. 
	 * 
	 * @return bool
	 */
	public function delete($oAccount, $iType, $sPath, $sName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.delete', array($oAccount, $iType, $sPath, $sName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->delete($oAccount, $iType, $sPath, $sName);
			if ($oAccount && $oAccount instanceof CAccount)
			{
				\CApi::ExecuteMethod('Min::DeleteMinByID', 
						array(
							'ID' => $this->oStorage->generateShareHash($oAccount, $iType, $sPath, $sName)
						)
				);
			}
		}
		
		return $bResult;
	}

	/**
	 * 
	 * @param CAccount $oAccount
	 * @param int $iType
	 * @param string $sPath
	 * @param string $sNewName
	 * @param int $iSize
	 * 
	 * @return array
	 */
	private function generateMinArray($oAccount, $iType, $sPath, $sNewName, $iSize)
	{
		$aData = null;
		if ($oAccount)
		{
			$aData = array(
				'AccountType' => $oAccount instanceof CAccount ? 'wm' : '',
				'Account' => 0,
				'Type' => $iType,
				'Path' => $sPath,
				'Name' => $sNewName,
				'Size' => $iSize
			);

			if (empty($aData['AccountType']) && $oAccount instanceof CHelpdeskUser)
			{
				$aData['AccountType'] = 'hd';
			}

			if ('wm' === $aData['AccountType'])
			{
				$aData['Account'] = $oAccount->IdAccount;
			}
			else if ('hd' === $aData['AccountType'])
			{
				$aData['Account'] = $oAccount->IdHelpdeskUser;
			}
		}

		return $aData;
	}
	
	/**
	 * Renames file or folder. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sName Name of file or folder. 
	 * @param string $sNewName New name. 
	 * @param bool $bIsLink
	 * 
	 * @return bool
	 */
	public function rename($oAccount, $iType, $sPath, $sName, $sNewName, $bIsLink)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.rename', array($oAccount, $iType, $sPath, $sName, $sNewName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $bIsLink ? $this->oStorage->renameLink($oAccount, $iType, $sPath, $sName, $sNewName) : $this->oStorage->rename($oAccount, $iType, $sPath, $sName, $sNewName);
			if ($bResult)
			{
				$sID = $this->oStorage->generateShareHash($oAccount, $iType, $sPath, $sName);
				$sNewID = $this->oStorage->generateShareHash($oAccount, $iType, $sPath, $sNewName);

				$mData = \CApi::ExecuteMethod('Min::GetMinByID', array('ID' => $sID));
				if ($mData && $oAccount)
				{
					$aData = $this->generateMinArray($oAccount, $iType, $sPath, $sNewName, $mData['Size']);
					if ($aData)
					{
						\CApi::ExecuteMethod('Min::UpdateMinByID', 
								array(
									'ID' => $sID,
									'Data' => $aData,
									'NewID' => $sNewID,
								)
						);
					}
				}
			}
		}
		return $bResult;
	}
	
	/**
	 * Move file or folder to a different location. In terms of Aurora, item can be moved to a different storage as well. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iFromType Source storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param int $iToType Destination storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sFromPath Path to the folder which contains the item. 
	 * @param string $sToPath Destination path of the item. 
	 * @param string $sName Current name of file or folder. 
	 * @param string $sNewName New name of the item. 
	 * 
	 * @return bool
	 */
	public function move($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.move', array($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$GLOBALS['__FILESTORAGE_MOVE_ACTION__'] = true;
			$bResult = $this->oStorage->copy($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName, true);
			$GLOBALS['__FILESTORAGE_MOVE_ACTION__'] = false;
			if ($bResult)
			{
				$sID = $this->oStorage->generateShareHash($oAccount, $iFromType, $sFromPath, $sName);
				$sNewID = $this->oStorage->generateShareHash($oAccount, $iToType, $sToPath, $sNewName);

				$mData = \CApi::ExecuteMethod('Min::GetMinByID', array('ID' => $sID));
				if ($mData)
				{
					$aData = $this->generateMinArray($oAccount, $iToType, $sToPath, $sNewName, $mData['Size']);
					if ($aData)
					{
						\CApi::ExecuteMethod('Min::UpdateMinByID', 
							array(
								'ID' => $sID,
								'Data' => $aData,
								'NewID' => $sNewID,
							)
						);
					}
				}
			}
		}
		return $bResult;
	}

	/**
	 * Copies file or folder, optionally renames it. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iFromType Source storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param int $iToType Destination storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sFromPath Path to the folder which contains the item. 
	 * @param string $sToPath Destination path of the item.
	 * @param string $sName Current name of file or folder. 
	 * @param string $sNewName New name of the item. 
	 * 
	 * @return bool
	 */
	public function copy($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName = null)
	{
		$bResult = false;
		$bBreak = false;
		\CApi::Plugin()->RunHook('filestorage.copy', array($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName, &$bResult, &$bBreak));
		if (!$bBreak)
		{
			$bResult = $this->oStorage->copy($oAccount, $iFromType, $iToType, $sFromPath, $sToPath, $sName, $sNewName);
		}
		return $bResult;
	}

	/**
	 * Returns user used space in bytes for specified storages.
	 * 
	 * @param int $iUserId User identificator.
	 * @param string $aTypes Storage type list. Accepted values in array: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**.
	 * 
	 * @return int;
	 */
	public function getUserUsedSpace($iUserId, $aTypes = array(EFileStorageTypeStr::Personal))
	{
		return $this->oStorage->getUserUsedSpace($iUserId, $aTypes);
	}
	
	/**
	 * Returns general quota information for the account, used and available space. 
	 * 
	 * @param CAccount $oAccount Account object
	 * 
	 * @return array array( $iUsageSize, $iFreeSize ); 
	 */
	public function getQuota($oAccount)
	{
		$iUsageSize = 0;
		$iFreeSize = 0;
		
		$oApiTenants = \CApi::GetSystemManager('tenants');
		$oTenant = $oApiTenants ? $oApiTenants->getTenantById($oAccount->IdTenant) : null;
		if ($oTenant)
		{
			$iUsageSize = $oTenant->FilesUsageInMB * 1024 * 1024;
			$iFreeSize = ($oTenant->FilesUsageDynamicQuotaInMB * 1024 * 1024) - $iUsageSize;
		}
		
		return array($iUsageSize, $iFreeSize);
	}
	
	/**
	 * Allows for obtaining filename which doesn't exist in current directory. For example, if you need to store **data.txt** file but it already exists, this method will return **data_1.txt**, or **data_2.txt** if that one already exists, and so on. 
	 * 
	 * @param CAccount $oAccount Account object 
	 * @param int $iType Storage type. Accepted values: **EFileStorageType::Personal**, **EFileStorageType::Corporate**, **EFileStorageType::Shared**. 
	 * @param string $sPath Path to the folder which contains the file, empty string means the file is in the root folder. 
	 * @param string $sFileName Filename. 
	 * 
	 * @return string
	 */
	public function getNonExistingFileName($oAccount, $iType, $sPath, $sFileName)
	{
		return $this->oStorage->getNonExistingFileName($oAccount, $iType, $sPath, $sFileName);
	}	
	
	/**
	 * 
	 * @param CAccount $oAccount
	 */
	public function clearPrivateFiles($oAccount)
	{
		$this->oStorage->clearPrivateFiles($oAccount);
	}

	/**
	 * 
	 * @param CAccount $oAccount
	 */
	public function clearCorporateFiles($oAccount)
	{
		$this->oStorage->clearPrivateFiles($oAccount);
	}

	/**
	 * 
	 * @param CAccount $oAccount
	 */
	public function clearAllFiles($oAccount)
	{
		$this->clearPrivateFiles($oAccount);
		$this->clearCorporateFiles($oAccount);
	}
}
