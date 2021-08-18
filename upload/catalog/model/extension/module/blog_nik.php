<?php
class ModelExtensionModuleBlogNik extends Model {
    public function getBlogSettings() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_settings`");

        return $query->row;
    }

    public function getTotalBlogArticles($data = array()) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.blog_article_id = ba2s.blog_article_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.status = '1'";

        if (!empty($data['filter_tags'])) {
            $sql .= " AND (";

            $implode = array();

            $words = explode(', ', trim(preg_replace('/\s+/', ' ', $data['filter_tags'])));

            foreach ($words as $word) {
                $implode[] = "bad.tags LIKE '%" . $this->db->escape($word) . "%'";
            }

            if ($implode) {
                $sql .= " " . implode(" OR ", $implode) . "";
            }

            $sql .= ")";
        }

        if (!empty($data['blog_article_id'])) {
            $sql .= ' AND ba.blog_article_id <> ' . $data['blog_article_id'];
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getBlogArticles($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.blog_article_id = ba2s.blog_article_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.status = '1'";

        if (!empty($data['filter_tags'])) {
            $sql .= " AND (";

            $implode = array();

            $words = explode(', ', trim(preg_replace('/\s+/', ' ', $data['filter_tags'])));

            foreach ($words as $word) {
                $implode[] = "bad.tags LIKE '%" . $this->db->escape($word) . "%'";
            }

            if ($implode) {
                $sql .= " " . implode(" OR ", $implode) . "";
            }

            $sql .= ")";
        }

        if (!empty($data['blog_article_id'])) {
            $sql .= ' AND ba.blog_article_id <> ' . $data['blog_article_id'];
        }

        $sort_data = array(
            'ba.sort_order',
            'ba.blog_article_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY ba.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(bad.title) DESC";
        } else {
            $sql .= " ASC, LCASE(bad.title) ASC";
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
    }

    public function getBlogArticle($blog_article_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.blog_article_id = ba2s.blog_article_id) WHERE ba.blog_article_id = '" . (int)$blog_article_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.status = '1' ORDER BY ba.sort_order, LCASE(bad.title)");

        return $query->row;
    }
}