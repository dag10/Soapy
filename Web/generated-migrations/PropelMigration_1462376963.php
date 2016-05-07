<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1462376963.
 * Generated on 2016-05-04 11:49:23 by drew
 */
class PropelMigration_1462376963
{
    public $comment = '';

    private function replaceSpotifyPlaylist($manager, $old, $new) {
      $sql = "UPDATE user
              SET playlist_id=" . $new['id'] . "
              WHERE playlist_id=" . $old['id'];
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();

      $sql = "UPDATE listensto
              SET playlist_id=" . $new['id'] . "
              WHERE playlist_id=" . $old['id'];
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();

      $sql = "DELETE FROM spotifyplaylist
              WHERE id=" . $old['id'];
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();

      $sql = "DELETE FROM playlist
              WHERE id=" . $old['id'];
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
    }

    private function deDupSpotifyPlaylist($manager, $uri) {
      $sql = "SELECT * from spotifyplaylist
              WHERE uri='$uri'
              ORDER BY id ASC";
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $sp_playlists = $stmt->fetchAll();
      $sp_playlist_keep = $sp_playlists[0];
      $sp_playlists_delete = array_slice($sp_playlists, 1);

      foreach ($sp_playlists_delete as $sp_playlist) {
        $this->replaceSpotifyPlaylist($manager, $sp_playlist, $sp_playlist_keep);
      }
    }

    public function preUp($manager)
    {
      $sql = "SELECT count(1) as count, uri
              FROM spotifyplaylist
              GROUP BY uri";
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();

      foreach ($stmt->fetchAll() as $res) {
        if ($res['count'] > 1) {
          $this->deDupSpotifyPlaylist($manager, $res['uri']);
        }
      }
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
      echo "DOWN MIGRATION NOT IMPLEMENTED. WOULD NEED TO DUPLICATE ROWS.\n";
      return false;
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'soapy' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `spotifyplaylist`

  ADD UNIQUE INDEX `spotifyplaylist_u_480850` (`uri`(80));

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'soapy' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `spotifyplaylist`

  DROP INDEX `spotifyplaylist_u_480850`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
