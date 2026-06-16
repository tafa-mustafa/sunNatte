<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required'    => 'Le nom est obligatoire.',
            'email.required'   => 'L\'email est obligatoire.',
            'email.email'      => 'Veuillez entrer une adresse email valide.',
            'subject.required' => 'Le sujet est obligatoire.',
            'message.required' => 'Le message est obligatoire.',
        ]);

        $body = "Nom: {$validated['name']}\n"
              . "Email: {$validated['email']}\n"
              . "Téléphone: " . ($validated['phone'] ?? 'Non renseigné') . "\n"
              . "Sujet: {$validated['subject']}\n\n"
              . "Message:\n{$validated['message']}\n\n"
              . "---\nEnvoyé depuis le formulaire de contact SUNUNATT";

        try {
            Mail::raw($body, function ($message) use ($validated) {
                $message->to('contact@teknon.sn')
                        ->subject('Contact SUNUNATT — ' . $validated['subject'])
                        ->replyTo($validated['email'], $validated['name']);
            });

            return back()->with('success', 'Votre message a bien été envoyé ! Nous vous répondrons sous 48h.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Une erreur est survenue lors de l\'envoi. Écrivez-nous directement à contact@teknon.sn.')
                ->withInput();
        }
    }
}
