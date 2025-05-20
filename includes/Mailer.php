<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configuration du serveur SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com'; // À configurer selon votre serveur
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'votre-email@gmail.com'; // À configurer
        $this->mailer->Password = 'votre-mot-de-passe-app'; // À configurer
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        // Configuration de l'expéditeur par défaut
        $this->mailer->setFrom('noreply@govathon.com', 'GOVATHON');
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }
    
    public function sendVerificationCode($to, $code) {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = 'Vérification de votre projet GOVATHON';
            
            // Template HTML de l'email
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #00843F;'>Vérification de votre projet GOVATHON</h2>
                    <p>Bonjour,</p>
                    <p>Merci d'avoir soumis votre projet sur GOVATHON.</p>
                    <p>Votre code de vérification est : <strong style='font-size: 20px; color: #00843F;'>{$code}</strong></p>
                    <p>Ce code est valable pendant 15 minutes.</p>
                    <p>Si vous n'avez pas soumis de projet, veuillez ignorer cet email.</p>
                    <hr style='border: 1px solid #eee; margin: 20px 0;'>
                    <p style='color: #666; font-size: 12px;'>
                        Cet email a été envoyé automatiquement, merci de ne pas y répondre.
                    </p>
                </div>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = "Votre code de vérification GOVATHON est : {$code}";
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $e->getMessage());
            throw new Exception("Erreur lors de l'envoi de l'email");
        }
    }
} 