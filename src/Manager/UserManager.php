<?php

namespace App\Manager;

use App\Entity\User;
use App\Manager\Base\EntityManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends EntityManager
{
    const Secret = 'Y7JC9GzUaHtX5asfdsvXWxUeDK2L5m55PyafAFWGEFGSVXCYgq8WgpnqfewgfbdgfaQyMYhagmxn3g2rkXkTJDsFCtHMx';

    protected $entityName = 'User';

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @required
     */
    public function inject(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentUser()
    {
        return parent::getCurrentUser();
    }

    /**
     * Change  password for the user
     *
     * @param User $user
     * @param string $password
     *
     * @return void
     */
    public function changePassword(User $user, $password)
    {
        $password = $this->getEncodedPassword($user, $password);

        $user->setPassword($password);
        $this->save($user);
    }

    /**
     * Check email activation token and enable user
     *
     * @param User $user
     * @param string $token
     * @param boolean $admin
     *
     * @return boolean
     */
    public function activate(User $user, $token, $admin = false)
    {
        if ($token != $this->getUserToken($user, 'account') && !$admin)
            return false;

        $user->enable();
        $this->save($user);

        return true;
    }

    /**
     * Create new user account and send activation email
     *
     * @param User $user
     */
    public function create($user)
    {
        $password = $user->getPassword();
        $user->setPassword(0);
        $this->save($user);
        $user->setPassword($this->getEncodedPassword($user, $password));
        $this->save($user);
    }

    /**
     * Get token of the user for the activate action
     *
     * @param User $user
     * @param string $type
     * @param string|null $value
     *
     * @return string
     */
    protected function getUserToken(User $user, $type, $value = null)
    {
        switch($type)
        {
            case 'account':

                return substr(sha1(sprintf('%d:account:%s', $user->getId(), self::Secret)), 5, 20);

            case 'password':

                return substr(sha1(sprintf('%d:password:%s', $user->getId(), $user->getPassword(), self::Secret)), 4, 20);

            case 'email':

                return substr(sha1(sprintf('%d:email:%s', $user->getId(), $value, self::Secret)), 3, 20);
        }

        return new \InvalidArgumentException();
    }

    /**
     * Get encoded version of user plain password
     *
     * @param User $user
     * @param string $password
     *
     * @return string
     */
    public function getEncodedPassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }
}
