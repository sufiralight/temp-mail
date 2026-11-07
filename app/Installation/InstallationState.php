<?php

namespace App\Installation;

enum InstallationState
{
    case Fresh;
    case Installed;
    case Recovery;
    case Uncertain;
}
