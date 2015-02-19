<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 * (c) Joseph Cohen <joseph.cohen@dinkbit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use StyleCI\StyleCI\Models\Repo;

/**
 * This is the shield controller class.
 *
 * @author James Brooks <jbrooksuk@me.com>
 */
class ShieldController extends AbstractController
{
    /**
     * Handles a request to serve a shield.
     *
     * @param \StyleCI\StyleCI\Models\Repo $repo
     * @param \Illuminate\Http\Request     $request
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Repo $repo, Request $request)
    {
        $shieldUrl = $this->generateShieldUrl($repo, $request);

        return Redirect::to($shieldUrl);
    }

    /**
     * Generates a Shields.io URL.
     *
     * @param \Illuminate\Http\Request     $repo
     * @param \StyleCI\StyleCI\Models\Repo $request
     *
     * @return string
     */
    protected function generateShieldUrl(Repo $repo, Request $request)
    {
        $url = 'https://img.shields.io/badge/%s-%s-%s.svg?style=%s';

        $color = 'lightgrey';
        $status = 'unknown';
        if ($commit = $repo->commits()->where('ref', 'refs/heads/master')->orderBy('created_at', 'desc')->first()) {
            $status = strtolower($commit->summary());
            if ($commit->status === 1) {
                $color = 'green';
            } elseif ($commit->status === 2) {
                $color = 'red';
            }
        }

        return vsprintf($url, [
            'StyleCI',
            $status,
            $color,
            $request->get('style', ''),
        ]);
    }
}