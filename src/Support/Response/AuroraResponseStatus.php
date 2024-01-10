<?php

namespace Minigyima\Aurora\Support\Response;


enum AuroraResponseStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case FAIL = 'fail';
}
