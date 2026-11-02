<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Les PDF / audios / vidéos d'exercices (contenu payant) étaient sur le disque public :
 * n'importe qui ayant le lien pouvait les télécharger. On les déplace sur le disque privé ;
 * ils sont désormais servis par la route protégée exercise.media.
 * Le chemin enregistré en base ne change pas (même chemin relatif, autre disque).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->move('public', 'private');
    }

    public function down(): void
    {
        $this->move('private', 'public');
    }

    private function move(string $from, string $to): void
    {
        $source = Storage::disk($from);
        $target = Storage::disk($to);

        DB::table('exercises')
            ->select(['id', 'pdf_path', 'audio_path', 'video_path'])
            ->orderBy('id')
            ->each(function ($row) use ($source, $target) {
                foreach ([$row->pdf_path, $row->audio_path, $row->video_path] as $path) {
                    if (! is_string($path) || $path === '' || ! $source->exists($path) || $target->exists($path)) {
                        continue;
                    }

                    $stream = $source->readStream($path);
                    if ($stream && $target->writeStream($path, $stream)) {
                        is_resource($stream) && fclose($stream);
                        $source->delete($path);
                    }
                }
            });
    }
};
