##User Entity

    /**
     * @ORM\Entity
     */
    class User extends AbstractUser
    {
        /**
         * @ORM\Id @ORM\Column(type="integer")
         * @ORM\GeneratedValue
         */
        protected $id;

    }


##Credentials Entity

    /**
     * @ORM\Entity
     */
    class Credentials extends AbstractCredentials
    {
        /**
         * Credentials ID
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

##Configuration Example

    #app/config.yml

    heidi_labs_sauth:
        user_class: AppBundle\Entity\User #this is the default value
        credentials_class: AppBundle\Entity\Credentials #this is the default value
        allow_registration: true #this is true by default
        services:
            google:
                class: HeidiLabs\SauthBundle\OAuthService\GoogleService
                config:
                    client_name: APPNAME
                    client_id: YOUR_GOOGLE_API_CLIENT_ID
                    client_secret: YOUR_GOOGLE_API_CLIENT_SECRET
                    callback_url: http://allowed_callback