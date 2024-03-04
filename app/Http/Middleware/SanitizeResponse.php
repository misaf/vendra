<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SanitizeResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);

        // $body = $response->getContent();

        // $replace = [
        //     //remove tabs before and after HTML tags
        //     '/\>[^\S ]+/s'   => '>',
        //     '/[^\S ]+\</s'   => '<',
        //     //shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
        //     '/([\t ])+/s'  => ' ',
        //     //remove leading and trailing spaces
        //     '/^([\t ])+/m' => '',
        //     '/([\t ])+$/m' => '',
        //     // remove JS line comments (simple only); do NOT remove lines containing URL (e.g. 'src="http://server.com/"')!!!
        //     '~//[a-zA-Z0-9 ]+$~m' => ' ',
        //     //remove empty lines (sequence of line-end and white-space characters)
        //     '/[\r\n]+([\t ]?[\r\n]+)+/s'  => "\n",
        //     //remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
        //     '/\>[\r\n\t ]+\</s'    => '><',
        //     //remove "empty" lines containing only JS's block end character; join with next line (e.g. "}\n}\n</script>" --> "}}</script>"
        //     '/}[\r\n\t ]+/s'             => '}',
        //     '/}[\r\n\t ]+,[\r\n\t ]+/s'  => '},',
        //     //remove new-line after JS's function or condition start; join with next line
        //     '/\)[\r\n\t ]?{[\r\n\t ]+/s'  => '){',
        //     '/,[\r\n\t ]?{[\r\n\t ]+/s'   => ',{',
        //     //remove new-line after JS's line end (only most obvious and safe cases)
        //     '/\),[\r\n\t ]+/s'  => '),',
        // ];

        // $body = preg_replace(array_keys($replace), array_values($replace), $body);

        // return $response->setContent($body);
    }
}
