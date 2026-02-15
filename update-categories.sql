-- Update categories to new naming
-- Mapping: Economy→Money, Environment→Markets, Health→Government, Science→Society, Technology→Freedom

UPDATE categories SET name = 'Money', slug = 'money' WHERE slug = 'economy';
UPDATE categories SET name = 'Markets', slug = 'markets' WHERE slug = 'environment';
UPDATE categories SET name = 'Government', slug = 'government' WHERE slug = 'health';
UPDATE categories SET name = 'Society', slug = 'society' WHERE slug = 'science';
UPDATE categories SET name = 'Freedom', slug = 'freedom' WHERE slug = 'technology';
