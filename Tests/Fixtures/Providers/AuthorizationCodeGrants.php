<?php


namespace Bytes\DiscordClientBundle\Tests\Fixtures\Providers;


use Bytes\Common\Faker\Providers\MiscProvider;
use Bytes\DiscordClientBundle\Services\OAuth;
use Bytes\DiscordResponseBundle\Enums\OAuthPrompts;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Faker\Generator;
use Faker\Provider\Base;
use Faker\Provider\Internet;
use Illuminate\Support\Arr;

/**
 * Class AuthorizationCodeGrants
 * @package Bytes\DiscordClientBundle\Tests\Fixtures\Providers
 *
 * @property Generator|Internet|MiscProvider $generator
 */
class AuthorizationCodeGrants extends Base
{
    /**
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $find = Arr::first($generator->getProviders(), function ($value, $key) {
            return get_class($value) === MiscProvider::class;
        });
        if (is_null($find)) {
            $generator->addProvider(new MiscProvider($generator));
        }
        parent::__construct($generator);
    }

    /**
     * @return array
     */
    public function permissionsAddRemove()
    {
        $permissions = self::permissions(0, true);
        $permissions = OAuth::hydratePermissions($permissions);
        array_walk($permissions, array($this, 'walkPermissionsToValue'));

        $end = self::numberBetween(0, count($permissions));
        if ($end > 0) { // To ensure this doesn't run twice with one result
            foreach (range(1, $end) as $i) {
                $add[] = array_shift($permissions);
            }
        }

        return [
            'add' => $add ?? [],
            'remove' => $permissions ?? [],
        ];
    }

    /**
     * @param int $max
     * @param bool $asStringArray
     * @return Permissions[]|string[]
     */
    public function permissions(int $max = 0, bool $asStringArray = false)
    {
        $permissions = Permissions::toArray();
        if ($max < 1) {
            $max = self::numberBetween(1, count($permissions));
        }
        $temp = self::randomElements($permissions, $max);
        if ($asStringArray) {
            return array_values($temp);
        }
        return OAuth::hydratePermissions($temp);
    }

    /**
     * @return array
     */
    public function scopesAddRemove()
    {
        $scopes = self::scopes();
        $scopes = OAuth::hydrateScopes($scopes);
        //array_walk($scopes, array(OAuth::class, 'walkHydrateScopes'));

        $end = self::numberBetween(0, count($scopes));
        if ($end > 0) { // To ensure this doesn't run twice with one result
            foreach (range(1, $end) as $i) {
                $add[] = array_shift($scopes);
            }
        }

        return [
            'add' => $add ?? [],
            'remove' => $scopes ?? [],
        ];
    }

    /**
     * @param int $max
     * @return array
     */
    public function scopes(int $max = 0)
    {
        $scopes = OAuthScopes::toArray();
        if ($max < 1) {
            $max = self::numberBetween(1, count($scopes));
        }
        return self::randomElements($scopes, $max);
    }

    /**
     * @return mixed|null
     */
    public function endpoint()
    {
        return self::randomElement(['bot', 'slash', 'user', 'login']);
    }

    /**
     * @return string
     */
    public function responseType()
    {
        return 'code';
    }

    /**
     * @return string
     */
    public function guildId()
    {
        return self::randomDigitNot(0) . self::randomNumber(8, true) . self::randomNumber(9, true);
    }

    /**
     * @return bool
     */
    public function disableGuildSelect()
    {
        return $this->generator->boolean();
    }

    /**
     * @return mixed|null
     */
    public function prompt()
    {
        return self::randomElement(OAuthPrompts::toArray());
    }

    /**
     * @return array
     */
    public function redirects()
    {
        $redirect = [
            'method' => self::randomElement(['route_name', 'url'])
        ];
        if ($redirect['method'] == 'route_name') {
            $redirect['route_name'] = $this->generator->camelWords();
        } else {
            $redirect['url'] = self::redirect();
        }

        return $redirect;
    }

    /**
     * @return mixed
     */
    public function redirect()
    {
        return $this->generator->url();
    }

    /**
     * @param $value
     * @param $key
     */
    protected function walkPermissionsToValue(&$value, $key)
    {
        $value = $value->value;
    }


}