<?php
namespace xBeastMode\Jager\Game;
use pocketmine\world\Position;
use xBeastMode\Jager\Utils;
class GameSettings implements \JsonSerializable{
        /** @var string */
        public $name;
        /** @var bool */
        public $auto_start;
        /** @var int */
        public $start_delay;
        /** @var int */
        public $game_time;
        /** @var int */
        public $needed_players;
        /** @var Position */
        public $sign_position;
        /** @var Position */
        public $innocents_position;
        /** @var Position */
        public $hunter_position;
        /** @var Position */
        public $spawn_position;
        /** @var Position*/
        public $game_lobby_position;

        /**
         * @param array $settings
         *
         * @return GameSettings
         */
        public static function parseFromArray(array $settings): GameSettings{
                $game_settings = new GameSettings();

                $game_settings->name = $settings["name"];
                $game_settings->auto_start = $settings["auto_start"];
                $game_settings->game_time = $settings["game_time"];

                $game_settings->start_delay = $settings["start_delay"];
                $game_settings->needed_players = $settings["needed"];

                $game_settings->sign_position = Utils::parsePosition($settings["sign"]);
                $game_settings->innocents_position = Utils::parsePosition($settings["innocents"]);

                $game_settings->hunter_position = Utils::parsePosition($settings["hunter"]);
                $game_settings->spawn_position = Utils::parsePosition($settings["spawn"]);

                $game_settings->game_lobby_position = Utils::parsePosition($settings["game_lobby"]);
                return $game_settings;
        }

        /**
         * @param string $yaml
         *
         * @return GameSettings
         */
        public static function parseFromYAML(string $yaml): GameSettings{
                return self::parseFromArray(yaml_parse($yaml));
        }

        /**
         * @param string $path
         *
         * @return GameSettings
         */
        public static function parseFromYAMLFile(string $path): GameSettings{
                return self::parseFromArray(yaml_parse_file($path));
        }

        /**
         * @param string $path
         */
        public function saveToYAMLFile(string $path){
                yaml_emit_file($path, $this->toDataArray());
        }

        /**
         * @param string $path
         */
        public function saveToJsonFile(string $path){
                file_put_contents($path, json_encode($this));
        }

        /**
         * @return array
         */
        public function toDataArray(): array{
                return [
                    "name" => $this->name,
                    "auto_start" => $this->auto_start,
                    "game_time" => $this->game_time,
                    "start_delay" => $this->start_delay,
                    "needed" => $this->needed_players,
                    "innocents" => Utils::positionToString($this->innocents_position),
                    "hunter" => Utils::positionToString($this->hunter_position),
                    "spawn" => Utils::positionToString($this->spawn_position),
                    "game_lobby" => Utils::positionToString($this->game_lobby_position),
                ];
        }

        public function jsonSerialize(): array{
                return $this->toDataArray();
        }
}