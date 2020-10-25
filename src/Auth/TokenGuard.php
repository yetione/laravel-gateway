<?php


namespace Yetione\Gateway\Auth;


use Yetione\Gateway\Http\Request;
use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

class TokenGuard implements Guard
{
    /**
     * @var Authenticatable|null
     */
    protected ?Authenticatable $user = null;

    protected TokenProvider $provider;

    protected Request $request;

    public function __construct(Request $request, TokenProvider $provider)
    {
        $this->request = $request;
        $this->provider = $provider;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function guest()
    {
        return !$this->check();
    }

    public function user()
    {
        if (null !== $this->user) {
            return $this->user;
        }
        if (!empty($header = $this->request->header('Authorization'))) {
            $this->user = $this->provider->retrieveByHeader($header);
        }
        return $this->user;
    }

    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
        return null;
    }

    public function validate(array $credentials = [])
    {
        throw new BadMethodCallException('Method not implemented.');
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }
}
