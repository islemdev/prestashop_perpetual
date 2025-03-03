<?php

namespace ps_metrics_module_v4_0_10\Http\Message\Encoding;

/**
 * Transform a regular stream into a chunked one.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class ChunkStream extends FilteredStream
{
    protected function readFilter() : string
    {
        return 'chunk';
    }
    protected function writeFilter() : string
    {
        return 'dechunk';
    }
    protected function fill() : void
    {
        parent::fill();
        if ($this->stream->eof()) {
            $this->buffer .= "0\r\n\r\n";
        }
    }
}
