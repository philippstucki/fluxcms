<?php

class bx_helpers_xsl {

    public static function obfuscateEmail($email) {
        list($user, $host) = explode('@', $email);
        return $user.'.DELETE.(at).REMOVE.'.$host.'.IGNOREME.COM';
    }

}
