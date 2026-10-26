<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Conversation
{
    /**
     * Builds the browser-only conversation map used by the Step 5 prototype.
     *
     * @return array<string, mixed>
     */
    public static function config(
        string $english_welcome,
        string $english_placeholder,
        string $english_notice,
        int $max_input_length
    ): array
    {
        return [
            'defaultLanguage' => 'en',
            'maxInputLength' => $max_input_length,
            'responseDelay' => 480,
            'ui' => [
                'en' => [
                    'welcome' => $english_welcome,
                    'inputPlaceholder' => $english_placeholder,
                    'notice' => $english_notice,
                    'privacy' => 'Privacy',
                    'suggestedQuestions' => 'Suggested questions',
                    'send' => 'Send message',
                    'you' => 'You',
                    'sabiTyping' => 'SABI is typing.',
                    'reset' => 'Start over',
                    'resetAnnouncement' => 'The conversation was restarted in English.',
                    'leadOfferYes' => 'Yes, please contact me',
                    'leadOfferNo' => 'Not right now',
                    'leadNamePrompt' => 'Great. What is your full name?',
                    'leadNameInvalid' => 'Please enter your full name using at least two characters.',
                    'leadProgramPrompt' => 'Which program are you interested in?',
                    'programNursing' => 'Professional Nursing',
                    'programPta' => 'Physical Therapist Assistant',
                    'programUndecided' => 'I am not sure yet',
                    'leadEmailPrompt' => 'What email address should SABER College use to contact you?',
                    'leadEmailInvalid' => 'That email address does not look valid. Please enter it again.',
                    'leadPhonePrompt' => 'What is the best phone number to reach you?',
                    'leadPhoneInvalid' => 'Please enter a valid phone number with 7 to 15 digits.',
                    'leadConsentPrompt' => 'Before sending your request: {consent}',
                    'leadConsentYes' => 'I agree',
                    'leadConsentNo' => 'I do not agree',
                    'leadConfirmPrompt' => 'Everything is ready. Would you like me to send your information to the SABER College team now?',
                    'leadConfirmYes' => 'Send my request',
                    'leadRestart' => 'Start again',
                    'leadCancel' => 'Cancel request',
                    'leadCancelled' => 'No problem. I did not save or send your contact information. We can continue exploring SABER College.',
                    'leadRestarted' => 'Let’s start again. What is your full name?',
                    'leadSending' => 'Sending...',
                    'leadUnavailable' => 'Contact capture is not available yet. You can use the official Request Information page.',
                    'fallback' => "I don't have a verified answer for that yet. I can help with programs, admissions, tuition, student services, applying, or contacting SABER College. For another question, please contact the College directly.",
                    'fallbackLinks' => [
                        self::link('Contact SABER College', 'Contactar a SABER College', '/about_saber_college/contact/'),
                        self::link('Request information', 'Solicitar información', '/request-info/'),
                    ],
                ],
                'es' => [
                    'welcome' => 'Hola, soy SABI, la guía virtual de SABER College. Puedo ayudarte a conocer nuestros programas, admisiones, opciones de matrícula y servicios estudiantiles. ¿Cómo puedo ayudarte?',
                    'inputPlaceholder' => 'Pregúntale a SABI sobre programas, admisiones, matrícula o servicios...',
                    'notice' => 'Información general únicamente. Los requisitos, costos, fechas y políticas pueden cambiar; confírmalos con SABER College y el School Catalog vigente. No compartas información personal sensible.',
                    'privacy' => 'Privacidad',
                    'suggestedQuestions' => 'Preguntas sugeridas',
                    'send' => 'Enviar mensaje',
                    'you' => 'Tú',
                    'sabiTyping' => 'SABI está escribiendo.',
                    'reset' => 'Comenzar de nuevo',
                    'resetAnnouncement' => 'La conversación se reinició en español.',
                    'leadOfferYes' => 'Sí, quiero que me contacten',
                    'leadOfferNo' => 'Ahora no',
                    'leadNamePrompt' => 'Perfecto. ¿Cuál es tu nombre completo?',
                    'leadNameInvalid' => 'Escribe tu nombre completo usando al menos dos caracteres.',
                    'leadProgramPrompt' => '¿Qué programa te interesa?',
                    'programNursing' => 'Professional Nursing',
                    'programPta' => 'Physical Therapist Assistant',
                    'programUndecided' => 'Todavía no estoy seguro',
                    'leadEmailPrompt' => '¿Qué correo electrónico debe usar SABER College para contactarte?',
                    'leadEmailInvalid' => 'Ese correo no parece válido. Por favor, escríbelo nuevamente.',
                    'leadPhonePrompt' => '¿Cuál es el mejor número de teléfono para comunicarse contigo?',
                    'leadPhoneInvalid' => 'Ingresa un teléfono válido que tenga entre 7 y 15 dígitos.',
                    'leadConsentPrompt' => 'Antes de enviar tu solicitud: {consent}',
                    'leadConsentYes' => 'Acepto',
                    'leadConsentNo' => 'No acepto',
                    'leadConfirmPrompt' => 'Todo está listo. ¿Quieres que envíe ahora tus datos al equipo de SABER College?',
                    'leadConfirmYes' => 'Enviar mi solicitud',
                    'leadRestart' => 'Comenzar de nuevo',
                    'leadCancel' => 'Cancelar solicitud',
                    'leadCancelled' => 'No hay problema. No guardé ni envié tus datos de contacto. Podemos seguir explorando SABER College.',
                    'leadRestarted' => 'Comencemos de nuevo. ¿Cuál es tu nombre completo?',
                    'leadSending' => 'Enviando...',
                    'leadUnavailable' => 'La captura de contacto aún no está disponible. Puedes usar la página oficial para solicitar información.',
                    'fallback' => 'Todavía no tengo una respuesta verificada para esa pregunta. Puedo ayudarte con programas, admisiones, matrícula, servicios estudiantiles, solicitudes o contacto. Para otra consulta, comunícate directamente con SABER College.',
                    'fallbackLinks' => [
                        self::link('Contact SABER College', 'Contactar a SABER College', '/about_saber_college/contact/'),
                        self::link('Request information', 'Solicitar información', '/request-info/'),
                    ],
                ],
            ],
            'initialTopics' => ['programs', 'admissions', 'tuition', 'services', 'contact'],
            'topics' => self::topics(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function topics(): array
    {
        $topics = [
            'greeting' => [
                'label' => ['en' => 'Hello', 'es' => 'Hola'],
                'keywords' => [
                    'en' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon'],
                    'es' => ['hola', 'buenos días', 'buenas tardes', 'saludos'],
                ],
                'answer' => [
                    'en' => "Hello! I'm SABI. I can guide you to verified SABER College information. What would you like to explore?",
                    'es' => '¡Hola! Soy SABI. Puedo orientarte hacia información verificada de SABER College. ¿Qué te gustaría consultar?',
                ],
                'links' => [],
                'next' => ['programs', 'admissions', 'tuition', 'services'],
            ],
            'programs' => [
                'label' => ['en' => 'Explore programs', 'es' => 'Conocer los programas'],
                'keywords' => [
                    'en' => ['programs', 'program', 'degrees', 'degree', 'courses', 'study options'],
                    'es' => ['programas', 'programa', 'carreras', 'carrera', 'grados', 'cursos', 'qué puedo estudiar'],
                ],
                'answer' => [
                    'en' => 'SABER College currently offers two Associate of Science programs: Professional Nursing and Physical Therapist Assistant. Select a program to review its official page and admissions information.',
                    'es' => 'SABER College ofrece actualmente dos programas de Associate of Science: Professional Nursing y Physical Therapist Assistant. Selecciona un programa para consultar su página oficial y la información de admisiones.',
                ],
                'links' => [self::link('View all programs', 'Ver todos los programas', '/saber_college_programs/')],
                'next' => ['nursing', 'pta', 'admissions'],
            ],
            'nursing' => [
                'label' => ['en' => 'Professional Nursing', 'es' => 'Professional Nursing'],
                'keywords' => [
                    'en' => ['professional nursing', 'nursing', 'nurse', 'registered nurse', 'rn program'],
                    'es' => ['enfermería', 'enfermera', 'enfermero', 'programa de enfermería'],
                ],
                'answer' => [
                    'en' => 'Professional Nursing is an intensive, full-time Associate of Science program described by SABER College as 80 credit hours and 1,725 clock hours. It combines classroom theory, laboratory learning, and clinical education. Confirm current requirements with Nursing Admissions and the School Catalog.',
                    'es' => 'Professional Nursing es un programa intensivo y de tiempo completo de Associate of Science, descrito por SABER College con 80 credit hours y 1,725 clock hours. Combina teoría, laboratorio y educación clínica. Confirma los requisitos vigentes con Nursing Admissions y el School Catalog.',
                ],
                'links' => [
                    self::link('Professional Nursing program', 'Programa Professional Nursing', '/saber_college_programs/professional-nursing-program/'),
                    self::link('Nursing admissions', 'Admisiones de Nursing', '/saber_college_programs/professional-nursing-program/nursing-admissions/'),
                ],
                'next' => ['admissions', 'tuition', 'apply', 'lead'],
            ],
            'pta' => [
                'label' => ['en' => 'Physical Therapist Assistant', 'es' => 'Physical Therapist Assistant'],
                'keywords' => [
                    'en' => ['physical therapist assistant', 'physical therapy assistant', 'pta program', 'pta'],
                    'es' => ['asistente de terapia física', 'terapia física', 'programa pta'],
                ],
                'answer' => [
                    'en' => 'Physical Therapist Assistant is an Associate of Science program described by SABER College as 1,785 clock hours. It combines theory, technical skills, and clinical education for entry-level PTA responsibilities under professional supervision. Confirm current requirements with PTA Admissions and the School Catalog.',
                    'es' => 'Physical Therapist Assistant es un programa de Associate of Science descrito por SABER College con 1,785 clock hours. Combina teoría, destrezas técnicas y educación clínica para funciones de nivel inicial bajo supervisión profesional. Confirma los requisitos vigentes con PTA Admissions y el School Catalog.',
                ],
                'links' => [
                    self::link('PTA program', 'Programa PTA', '/saber_college_programs/physical-therapist-assistant-program/'),
                    self::link('PTA admissions', 'Admisiones de PTA', '/saber_college_programs/physical-therapist-assistant-program/admissions-pta/'),
                ],
                'next' => ['admissions', 'tuition', 'apply', 'lead'],
            ],
            'historical' => [
                'label' => ['en' => 'Historical programs', 'es' => 'Programas históricos'],
                'keywords' => [
                    'en' => ['medical assisting', 'medical assistant', 'esol', 'vesl'],
                    'es' => ['asistente médico', 'inglés esol', 'inglés vesl'],
                ],
                'answer' => [
                    'en' => 'Medical Assisting and ESOL/VESL are historical programs and are not part of the current active academic offer documented for this chatbot. Please confirm any availability question directly with Admissions.',
                    'es' => 'Medical Assisting y ESOL/VESL son programas históricos y no forman parte de la oferta académica activa documentada para este chatbot. Confirma cualquier consulta de disponibilidad directamente con Admissions.',
                ],
                'links' => [self::link('Contact Admissions', 'Contactar a Admissions', '/about_saber_college/contact/')],
                'next' => ['programs', 'admissions'],
            ],
            'admissions' => [
                'label' => ['en' => 'Admissions', 'es' => 'Admisiones'],
                'keywords' => [
                    'en' => ['admissions', 'admission', 'requirements', 'requirement', 'enroll', 'enrollment', 'how do i start'],
                    'es' => ['admisiones', 'admisión', 'requisitos', 'requisito', 'inscripción', 'inscribirme', 'cómo comienzo'],
                ],
                'answer' => [
                    'en' => 'Admissions requirements depend on the program. Use the official admissions pages and current School Catalog for the applicable requirements, then contact SABER College if you need confirmation before applying.',
                    'es' => 'Los requisitos de admisión dependen del programa. Consulta las páginas oficiales de admisiones y el School Catalog vigente, y comunícate con SABER College si necesitas confirmación antes de solicitar.',
                ],
                'links' => [
                    self::link('Admissions and tuition', 'Admisiones y matrícula', '/admissions-and-tuition/'),
                    self::link('Apply to SABER College', 'Solicitar a SABER College', '/apply-to-saber-college/'),
                ],
                'next' => ['nursing', 'pta', 'tuition', 'apply', 'lead'],
            ],
            'tuition' => [
                'label' => ['en' => 'Tuition and payment', 'es' => 'Matrícula y pagos'],
                'keywords' => [
                    'en' => ['tuition', 'cost', 'costs', 'price', 'payment', 'financial aid', 'net price'],
                    'es' => ['matrícula', 'costo', 'costos', 'precio', 'pago', 'ayuda financiera', 'precio neto'],
                ],
                'answer' => [
                    'en' => 'Tuition, fees, and financial information can change. Review the official payment options and Net Price Calculator, and confirm current figures with SABER College before making a decision.',
                    'es' => 'La matrícula, los cargos y la información financiera pueden cambiar. Consulta las opciones oficiales de pago y el Net Price Calculator, y confirma los valores vigentes con SABER College antes de tomar una decisión.',
                ],
                'links' => [
                    self::link('Tuition payment options', 'Opciones de pago', '/general/tuition-payment-options/'),
                    self::link('Net Price Calculator', 'Net Price Calculator', '/general/net-price-calculator/'),
                ],
                'next' => ['admissions', 'apply', 'contact', 'lead'],
            ],
            'services' => [
                'label' => ['en' => 'Student services', 'es' => 'Servicios estudiantiles'],
                'keywords' => [
                    'en' => ['student services', 'career services', 'transcript', 'academic affairs', 'student support'],
                    'es' => ['servicios estudiantiles', 'servicios al estudiante', 'carrera profesional', 'transcripción', 'asuntos académicos', 'apoyo estudiantil'],
                ],
                'answer' => [
                    'en' => 'The Student Services area connects visitors with Academic Affairs, Career Services, transcript requests, the SABER survey, and other student resources.',
                    'es' => 'El área de Student Services conecta a los visitantes con Academic Affairs, Career Services, solicitudes de transcript, la encuesta de SABER y otros recursos estudiantiles.',
                ],
                'links' => [self::link('Explore student services', 'Explorar servicios estudiantiles', '/student-services/')],
                'next' => ['visa', 'contact', 'programs'],
            ],
            'visa' => [
                'label' => ['en' => 'VISA I-20 assistance', 'es' => 'Asistencia VISA I-20'],
                'keywords' => [
                    'en' => ['visa', 'i-20', 'i20', 'international student'],
                    'es' => ['visa', 'i-20', 'i20', 'estudiante internacional'],
                ],
                'answer' => [
                    'en' => 'SABER College provides an official VISA I-20 Assistance page for prospective international students. SABI cannot provide legal or immigration advice; use the official page and contact the College for current guidance.',
                    'es' => 'SABER College tiene una página oficial de asistencia VISA I-20 para futuros estudiantes internacionales. SABI no ofrece asesoría legal ni migratoria; consulta la página oficial y contacta al College para recibir orientación vigente.',
                ],
                'links' => [self::link('VISA I-20 assistance', 'Asistencia VISA I-20', '/general/visa-i-20-assistance/')],
                'next' => ['admissions', 'contact'],
            ],
            'apply' => [
                'label' => ['en' => 'How to apply', 'es' => 'Cómo solicitar'],
                'keywords' => [
                    'en' => ['apply', 'application', 'start application', 'request information'],
                    'es' => ['aplicar', 'solicitud', 'enviar solicitud', 'solicitar información'],
                ],
                'answer' => [
                    'en' => 'You can begin with the official Apply to SABER College page. If you are still exploring your options, use Request Information so the College can follow up with you.',
                    'es' => 'Puedes comenzar en la página oficial Apply to SABER College. Si todavía estás explorando opciones, usa Request Information para que el College pueda contactarte.',
                ],
                'links' => [
                    self::link('Apply to SABER College', 'Solicitar a SABER College', '/apply-to-saber-college/'),
                    self::link('Request information', 'Solicitar información', '/request-info/'),
                ],
                'next' => ['admissions', 'tuition', 'contact', 'lead'],
            ],
            'accreditation' => [
                'label' => ['en' => 'Accreditation', 'es' => 'Acreditación'],
                'keywords' => [
                    'en' => ['accreditation', 'accredited', 'licensing'],
                    'es' => ['acreditación', 'acreditado', 'licencia institucional'],
                ],
                'answer' => [
                    'en' => 'Accreditation and licensing information should be reviewed on the official SABER College Accreditation page, where the current institutional details and references are maintained.',
                    'es' => 'La información de acreditación y licencias debe consultarse en la página oficial SABER College Accreditation, donde se mantienen los detalles y referencias institucionales vigentes.',
                ],
                'links' => [self::link('Accreditation information', 'Información de acreditación', '/saber-college-accreditation/')],
                'next' => ['programs', 'contact'],
            ],
            'contact' => [
                'label' => ['en' => 'Contact SABER', 'es' => 'Contactar a SABER'],
                'keywords' => [
                    'en' => ['contact', 'phone', 'email', 'address', 'location', 'campus', 'where are you'],
                    'es' => ['contacto', 'teléfono', 'correo', 'dirección', 'ubicación', 'campus', 'dónde están'],
                ],
                'answer' => [
                    'en' => 'SABER College is located at 3990 West Flagler Street, Suite 103, Miami, Florida 33134. Main phone: (305) 443-9170. General email: saber@sabercollege.edu.',
                    'es' => 'SABER College está ubicado en 3990 West Flagler Street, Suite 103, Miami, Florida 33134. Teléfono principal: (305) 443-9170. Correo general: saber@sabercollege.edu.',
                ],
                'links' => [
                    self::link('Contact page', 'Página de contacto', '/about_saber_college/contact/'),
                    self::link('Campus location', 'Ubicación del campus', '/campus-location/'),
                ],
                'next' => ['programs', 'admissions', 'apply'],
            ],
            'lead' => [
                'label' => ['en' => 'Request information', 'es' => 'Solicitar información'],
                'keywords' => [
                    'en' => ['contact me', 'call me', 'request information', 'talk to admissions', 'more information', 'i am interested', 'i want to enroll', 'i want to apply'],
                    'es' => ['contáctenme', 'llámenme', 'solicitar información', 'hablar con admisiones', 'más información', 'me interesa', 'quiero inscribirme', 'quiero aplicar'],
                ],
                'answer' => [
                    'en' => 'It sounds like you may be interested in taking the next step. Would you like the SABER College team to contact you? I will only ask for basic contact information if you say yes.',
                    'es' => 'Parece que podrías estar interesado en dar el siguiente paso. ¿Quieres que el equipo de SABER College se comunique contigo? Solo te pediré datos básicos de contacto si respondes que sí.',
                ],
                'links' => [],
                'next' => [],
                'action' => 'lead_offer',
            ],
            'privacy' => [
                'label' => ['en' => 'Privacy and safety', 'es' => 'Privacidad y seguridad'],
                'keywords' => [
                    'en' => ['social security', 'ssn', 'credit card', 'password', 'passport number', 'medical record'],
                    'es' => ['seguro social', 'tarjeta de crédito', 'contraseña', 'número de pasaporte', 'historia médica'],
                ],
                'answer' => [
                    'en' => 'Please do not share Social Security numbers, payment card details, passwords, medical records, immigration document numbers, or other sensitive personal information in this chat.',
                    'es' => 'No compartas números de Seguro Social, datos de tarjetas, contraseñas, registros médicos, números de documentos migratorios ni otra información personal sensible en este chat.',
                ],
                'links' => [self::link('Privacy Policy', 'Política de privacidad', '/privacy-policy/')],
                'next' => ['programs', 'contact'],
            ],
        ];
        $source_map = [
            'greeting' => ['programs-page', 'contact-page'],
            'programs' => ['catalog-2026', 'programs-page'],
            'nursing' => ['catalog-2026', 'nursing-handbook-2025', 'nursing-page'],
            'pta' => ['catalog-2026', 'pta-handbook-2025', 'pta-page'],
            'historical' => ['catalog-2026', 'contact-page'],
            'admissions' => ['catalog-2026', 'nursing-admissions-page', 'pta-admissions-page', 'admissions-tuition-page'],
            'tuition' => ['catalog-2026', 'admissions-tuition-page', 'tuition-payment-page'],
            'services' => ['student-services-page'],
            'visa' => ['visa-page'],
            'apply' => ['apply-page', 'admissions-tuition-page'],
            'accreditation' => ['accreditation-page'],
            'contact' => ['contact-page'],
            'lead' => ['apply-page', 'contact-page'],
            'privacy' => ['privacy-page'],
        ];
        foreach ($source_map as $topic_key => $source_keys) {
            if (isset($topics[$topic_key])) {
                $topics[$topic_key]['sources'] = $source_keys;
            }
        }

        return $topics;
    }

    /**
     * @return array<string, mixed>
     */
    private static function link(string $english_label, string $spanish_label, string $path): array
    {
        return [
            'label' => ['en' => $english_label, 'es' => $spanish_label],
            'url' => home_url($path),
        ];
    }
}
