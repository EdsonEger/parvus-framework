<?php
    namespace Parvus;

    use PHPMailer\PHPMailer\PHPMailer;

    class Mail
    {
        private $mailer, $aConfig, $html, $optOut;
        private $withLog = false;

        /**
         * Mail constructor.
         * @param null $prAConfig
         */
        public final function __construct($prAConfig = NULL)
        {
            /** Read the config */
            $this->aConfig = include(path.'app/config/Mail.php');

            /** New classe mailer */
            $this->mailer = new PHPMailer();

            /** Define SMTP */
            $this->mailer->isSMTP();
            $this->mailer->isHTML(true);
        }

				/**
         * @return \PHPMailer
         */
        public final function getPHPMailer ()
        {

            return $this->mailer;

        }

        /**
         * @param $prId
         */
        public final function setMessageId ($prId)
        {

            $this->mailer->MessageID = $prId;

        }

        /**
         * @param $prName
         * @param $prMail
         */
        public final function setFrom ($prName,$prMail)
        {

            $this->aConfig['from']['email'] = $prMail;
            $this->aConfig['from']['name']  = $prName;

        }

        /**
         * @param $prSMTPSecure
         */
        public final function setSMTPSecure ($prSMTPSecure)
        {

            $this->aConfig['SMTPSecure'] = $prSMTPSecure;

        }

        /**
         * @param $prPort
         */
        public final function setPort ($prPort)
        {

            $this->aConfig['port'] = $prPort;

        }

        /**
         * @param $prUser
         */
        public final function setUser ($prUser)
        {

            $this->aConfig['user'] = $prUser;

        }

        /**
         * @param $prPassword
         */
        public final function setPassword ($prPassword)
        {

            $this->aConfig['password'] = $prPassword;

        }

        /**
         * @param $prHost
         */
        public final function setHost ($prHost)
        {

            $this->aConfig['host'] = $prHost;

        }

        /**
         * @param $prView
         * @param $prArray
         */
        public final function setView ($prView, $prArray = array())
        {

            $this->aConfig['view']       = $prView;
            $this->aConfig['view-array'] = $prArray;

        }

        /**
         * Add a reply to
         * @param $prMail
         * @param $prName
         */
        public final function replyTo ($prMail, $prName = NULL)
        {

            $this->mailer->addReplyTo($prMail,$prName);

        }

        /**
         * Add a attachment
         * @param $prFile
         * @param null $prName
         */
        public final function attachment ($prFile,$prName = NULL)
        {
            $this->mailer->addAttachment($prFile,$prName);
        }

        /**
         * Add a attachment as string
         * @param string $prFile
         * @param null $prName
         */
        public final function attachmentString ($prFile,$prName = NULL)
        {
            $this->mailer->addStringAttachment($prFile,$prName);
        }

        /**
         * Add a BCC
         * @param $prMail
         */
        public final function bcc ($prMail)
        {
            $this->mailer->addBCC($prMail);
        }

        /**
         * Add a CC
         * @param $prMail
         */
        public final function cc ($prMail)
        {
            $this->mailer->addCC($prMail);
        }

        /**
         * Add a mail address
         * @param $prMail
         * @param $prName
         */
        public final function address ($prMail,$prName = NULL)
        {
            $this->mailer->addAddress($prMail,$prName);
        }

        /**
         * Define the subject
         * @param $prSubject
         */
        public final function subject ($prSubject)
        {
            $this->mailer->Subject = $prSubject;
        }

        /**
         * Define the mail content
         * @param $prHTML
         */
        public final function body ($prHTML)
        {
            $this->html = $prHTML;
        }

        /**
         * Define the opt out link
         * @param $prOptOut
         */
        public final function optOut ($prOptOut)
        {
            $this->optOut = $prOptOut;
        }

        /**
         * Define show log, not sending the mail
         */
        public final function withLog ()
        {

            $this->withLog = true;

        }

        /**
         * Sent the mail
         * @param bool $prForceSend
         * @return bool
         * @throws \phpmailerException
         */
        public final function sent ($prForceSend = false)
        {

            /** Config the connection with the server */
            $this->mailer->SMTPAuth     = true;
            $this->mailer->Host         = gethostbyname($this->aConfig['host']);
            $this->mailer->Password     = $this->aConfig['password'];
            $this->mailer->Username     = $this->aConfig['user'];
            $this->mailer->Port         = $this->aConfig['port'];
            $this->mailer->CharSet      = 'UTF-8';

            /** Disable SSL verification */
            $this->mailer->SMTPOptions = array (
                'ssl' => array (
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            /** TLS ou SSL */
            if ($this->aConfig['SMTPSecure'] != NULL)
            {

                $this->mailer->SMTPSecure = mb_strtolower($this->aConfig['SMTPSecure'],'UTF-8');

            }

            /** From */
            $this->mailer->From     = $this->aConfig['from']['email'];
            $this->mailer->FromName = $this->aConfig['from']['name'];

            /** Create a new view */
            $view = new \Parvus\View();

            /** If is not array */
            if ($this->aConfig['view-array'] == NULL)
            {

                $this->aConfig['view-array'] = array();

            }

            /** Add default param */
            $this->aConfig['view-array']['subject'] = $this->mailer->Subject;
            $this->aConfig['view-array']['html']    = $this->html;
            $this->aConfig['view-array']['optout']  = $this->optOut;

            /** Generate the HTML with Blade */
            $this->mailer->Body = $view->render ($this->aConfig['view'],$this->aConfig['view-array']);

            /** If has logging */
            if ($this->withLog)
            {

                print('<pre>');
                print_r($this->mailer);
                print('</pre>');

                return true;
            }

            /** If is local and not force send **/
            if (environment == 'local' && !$prForceSend)
            {
                return true;
            }

            if ($this->mailer->send())
            {

                $this->mailer->clearAddresses();
                $this->mailer->clearAttachments();
                $this->mailer->clearBCCs();
                $this->mailer->clearCCs();
                $this->mailer->clearCustomHeaders();
                $this->mailer->clearReplyTos();
                $this->mailer->clearAllRecipients();

                return true;

            }

            return false;

        }

        /**
         * Returns the error message
         **/
        public final function getError ()
        {

            return $this->mailer->ErrorInfo;

        }

    }
