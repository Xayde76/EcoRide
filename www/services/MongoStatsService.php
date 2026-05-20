<?php

class MongoStatsService {
    private static function getUri(): string {
        // MONGO_URI en priorité (MongoDB Atlas : mongodb+srv://...)
        $uri = getenv('MONGO_URI');
        if ($uri) return $uri;
        // Sinon fallback hostname simple (local / docker)
        $host = getenv('MONGO_HOST') ?: 'mongodb';
        return "mongodb://{$host}:27017";
    }

    private static function getManager(): ?MongoDB\Driver\Manager {
        try {
            return new MongoDB\Driver\Manager(self::getUri(), ['serverSelectionTimeoutMS' => 5000]);
        } catch (\Exception $e) {
            LoggerService::warning('MongoDB connection failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public static function recordTripCompleted(): void {
        $manager = self::getManager();
        if (!$manager) return;
        try {
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->update(
                ['date' => date('Y-m-d')],
                [
                    '$inc'         => ['nb_covoiturages' => 1],
                    '$setOnInsert' => ['credits_gagnes'  => 0],
                ],
                ['upsert' => true]
            );
            $manager->executeBulkWrite('ecoride_stats.stats', $bulk);
        } catch (\Exception $e) {
            LoggerService::warning('MongoDB stats write failed', ['error' => $e->getMessage()]);
        }
    }

    public static function recordCreditsEarned(int $amount): void {
        $manager = self::getManager();
        if (!$manager) return;
        try {
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->update(
                ['date' => date('Y-m-d')],
                [
                    '$inc'         => ['credits_gagnes'  => $amount],
                    '$setOnInsert' => ['nb_covoiturages' => 0],
                ],
                ['upsert' => true]
            );
            $manager->executeBulkWrite('ecoride_stats.stats', $bulk);
        } catch (\Exception $e) {
            LoggerService::warning('MongoDB stats write failed', ['error' => $e->getMessage()]);
        }
    }
}
