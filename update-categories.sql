-- Update categories to English
UPDATE categories SET name = 'Health', slug = 'health' WHERE slug = 'zdravi';
UPDATE categories SET name = 'Economy', slug = 'economy' WHERE slug = 'ekonomika';
UPDATE categories SET name = 'Science', slug = 'science' WHERE slug = 'veda';
UPDATE categories SET name = 'Technology', slug = 'technology' WHERE slug = 'technologie';
UPDATE categories SET name = 'Environment', slug = 'environment' WHERE slug = 'zivotni-prostredi';
