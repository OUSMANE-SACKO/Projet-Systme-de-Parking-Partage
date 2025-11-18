<?php
interface IDatabaseFactory {
    public static function getConnection();
    public static function closeConnection(): void;
}
?>