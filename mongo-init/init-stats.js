db = db.getSiblingDB('ecoride_stats');
db.createCollection('stats');
db.stats.createIndex({ date: 1 }, { unique: true });
print('MongoDB initialisé : collection stats prête.');
