<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Mbo\Addons\User;

use PhpEncryptionCore as PhpEncryption;

class CredentialsEncryptor
{
    /**
     * @var PhpEncryption
     */
    private $encryptor;

    public function __construct()
    {
        $this->encryptor = new PhpEncryption(_NEW_COOKIE_KEY_);
    }

    public function encrypt($value)
    {
        return $this->encryptor->encrypt($value);
    }

    public function decrypt($value)
    {
        return $this->encryptor->decrypt($value);
    }
}
