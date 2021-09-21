<?php
class ModelExtensionModuleBlogNik extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "blog_article` (
			`blog_article_id` INT(11) NOT NULL AUTO_INCREMENT,
			`image` VARCHAR(255) NOT NULL,
			`sort_order` INT(3) NOT NULL DEFAULT 0,
			`status` TINYINT(1) NOT NULL DEFAULT 1,
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`blog_article_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "blog_article_description` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `blog_article_id` INT(11) NOT NULL,
            `language_id` INT(11) NOT NULL,
            `title` VARCHAR(64) NOT NULL,
            `description` mediumtext NOT NULL,
            `meta_title` VARCHAR(255) NOT NULL,
            `meta_description` VARCHAR(255) NOT NULL,
            `meta_keyword` VARCHAR(255) NOT NULL,
            `tags` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`, `language_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "blog_article_to_layout` (
            `blog_article_id` INT(11) NOT NULL AUTO_INCREMENT,
            `store_id` INT(11) NOT NULL,
            `layout_id` INT(11) NOT NULL,
            PRIMARY KEY (`blog_article_id`, `store_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "blog_article_to_store` (
            `blog_article_id` INT(11) NOT NULL AUTO_INCREMENT,
            `store_id` INT(11) NOT NULL,
            PRIMARY KEY (`blog_article_id`, `store_id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "blog_settings` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `sort` INT(5) NOT NULL,
            `order` INT(5) NOT NULL,
            `limit_articles` INT(11) NOT NULL,
            `limit_related_articles` INT(11) NOT NULL,
            `show_tags` INT(1) NOT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "blog_article`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "blog_article_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "blog_article_to_layout`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "blog_article_to_store`");

        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query LIKE 'blog_article_id=%'");

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "blog_settings`");
    }

    public function saveBlogSettings($data) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_settings`");

        $this->db->query("INSERT INTO " . DB_PREFIX . "blog_settings SET `sort` = '" . (int)$data['sort'] . "', `order` = '" . (int)$data['order'] . "', `limit_articles` = '" . (int)$data['limit_articles'] . "', `limit_related_articles` = '" . (int)$data['limit_related_articles'] . "', `show_tags` = '" . (int)$data['show_tags'] . "'");
    }

    public function getBlogSettings() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_settings`");

        return $query->row;
    }

    // Blog Articles Functions

    public function addBlogArticle($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article SET `image` = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

        $blog_article_id = $this->db->getLastId();

        foreach ($data['blog_article_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_description SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', `tags` = '" . $this->db->escape($value['tags']) . "'");
        }

        if (isset($data['blog_article_store'])) {
            foreach ($data['blog_article_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_to_store SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        // SEO URL
        if (isset($data['blog_article_seo_url'])) {
            foreach ($data['blog_article_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'blog_article_id=" . (int)$blog_article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        if (isset($data['blog_article_layout'])) {
            foreach ($data['blog_article_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_to_layout SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        $this->cache->delete('blog_article');

        return $blog_article_id;
    }

    public function editBlogArticle($blog_article_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "blog_article SET `image` = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_description WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        foreach ($data['blog_article_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_description SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', `tags` = '" . $this->db->escape($value['tags']) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_to_store WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        if (isset($data['blog_article_store'])) {
            foreach ($data['blog_article_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_to_store SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_article_id=" . (int)$blog_article_id . "'");

        if (isset($data['blog_article_seo_url'])) {
            foreach ($data['blog_article_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (trim($keyword)) {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'blog_article_id=" . (int)$blog_article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        if (isset($data['blog_article_layout'])) {
            foreach ($data['blog_article_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_layout` SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        $this->cache->delete('blog_article');
    }

    public function deleteBlogArticle($blog_article_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_description` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_store` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'blog_article_id=" . (int)$blog_article_id . "'");

        $this->cache->delete('blog_article');
    }

    public function getBlogArticleInfo($blog_article_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.blog_article_id = ba2s.blog_article_id) WHERE ba.blog_article_id = '" . (int)$blog_article_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getBlogArticle($blog_article_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "blog_article WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        return $query->row;
    }

    public function getBlogArticles($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $sort_data = array(
                'bad.title',
                'ba.sort_order',
                'ba.blog_article_id',
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY bad.title";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }


            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $blog_articles_data = $this->cache->get('blog_article.' . (int)$this->config->get('config_language_id'));

            if (!$blog_articles_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bad.title");

                $blog_articles_data = $query->rows;

                $this->cache->set('blog_article.' . (int)$this->config->get('config_language_id'), $blog_articles_data);
            }

            return $blog_articles_data;
        }
    }

    public function getBlogArticleDescription($blog_article_id) {
        $blog_article_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_description WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        foreach ($query->rows as $result) {
            $blog_article_description_data[$result['language_id']] = array(
                'title'            => $result['title'],
                'description'      => $result['description'],
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
                'tags'             => $result['tags']
            );
        }

        return $blog_article_description_data;
    }

    public function getBlogArticleStores($blog_article_id) {
        $blog_article_store_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_to_store WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        foreach ($query->rows as $result) {
            $blog_article_store_data[] = $result['store_id'];
        }

        return $blog_article_store_data;
    }

    public function getBlogArticleSeoUrls($blog_article_id) {
        $blog_article_seo_url_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_article_id=" . (int)$blog_article_id . "'");

        foreach ($query->rows as $result) {
            $blog_article_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
        }

        return $blog_article_seo_url_data;
    }

    public function getBlogArticleLayouts($blog_article_id) {
        $blog_article_layout_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_to_layout WHERE blog_article_id = '" . (int)$blog_article_id . "'");

        foreach ($query->rows as $result) {
            $blog_article_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $blog_article_layout_data;
    }

    public function getTotalBlogArticles() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_article");

        return $query->row['total'];
    }

    public function getTotalBlogArticlesByLayoutId($layout_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_article_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

        return $query->row['total'];
    }
}