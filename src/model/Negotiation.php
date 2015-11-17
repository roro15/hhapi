<?php

namespace hh\model;

use hh\base\Model;

class Negotiation extends Model {

    protected $resume;
    protected $messages;

    public function getResume() {
        if (is_null($this->resume) && !empty($this->getRaw()->resume)) {
            $query = new ResumeQuery($this->getClient());
            $this->resume = $query
                    ->setAbsoluteUrl($this->getRaw()->resume->url)
                    ->one();
        }

        return $this->resume;
    }

    public function getMessages() {
        if (is_null($this->messages) && !empty($this->getRaw()->messages_url)) {
            $query = new NegotiationMessageQuery($this->getClient(), $this->getRaw()->messages_url);
            $this->messages = $query->collection();
        }
        
        return $this->messages;
    }

}
