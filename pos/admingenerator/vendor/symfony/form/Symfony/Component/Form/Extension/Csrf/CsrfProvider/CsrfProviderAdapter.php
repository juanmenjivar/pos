<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Csrf\CsrfProvider;

use Symfony\Component\Form\Exception\BadMethodCallException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Adapter for using old CSRF providers where the new {@link CsrfTokenManagerInterface}
 * is expected.
 *
 * @since  2.4
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.4, to be removed in Symfony 3.0.
 */
class CsrfProviderAdapter implements CsrfTokenManagerInterface
{
    /**
     * @var CsrfProviderInterface
     */
    private $csrfProvider;

    public function __construct(CsrfProviderInterface $csrfProvider)
    {
        $this->csrfProvider = $csrfProvider;
    }

    public function getCsrfProvider()
    {
        return $this->csrfProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($tokenId)
    {
        return new CsrfToken($tokenId, $this->csrfProvider->generateCsrfToken($tokenId));
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken($tokenId)
    {
        throw new BadMethodCallException('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken($tokenId)
    {
        throw new BadMethodCallException('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid(CsrfToken $token)
    {
        return $this->csrfProvider->isCsrfTokenValid($token->getId(), $token->getValue());
    }
}
