<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mageflow\Connect\Model\Oauth\Signature;

use Mageflow\Connect\Model\Oauth\Request;
use Mageflow\Connect\Model\Oauth\Token;
use Mageflow\Connect\Model\Oauth\Consumer;

/**
 *
 * @author Sven Varkel <sven@mageflow.com>
 */
interface SignatureBuilder
{

    /**
     * Sign  request
     *
     * @param \Mageflow\Connect\Model\Oauth\Request $request
     * @param \Mageflow\Connect\Model\Oauth\Consumer $consumer
     * @param \Mageflow\Connect\Model\Oauth\Token $token
     */
    public function buildSignature(Request $request, Consumer $consumer,
        Token $token);
}