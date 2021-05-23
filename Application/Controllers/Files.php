<?php

namespace Controllers;

use Models\File;
use Shared\Controller;
use Fonts\Proxy;
use Fonts\Types;

class Files extends Controller
{
    public function fonts($name)
    {
        $path = "/fonts";

        if (!file_exists("{$path}/{$name}")) {
            $proxy = new Proxy();

            $proxy->addFontTypes("{$name}", [
                Types::OTF => "{$path}/{$name}.otf",
                Types::EOT => "{$path}/{$name}.eot",
                Types::TTF => "{$path}/{$name}.ttf"
            ]);

            $weight = "";
            $style = "";
            $font = explode("-", $name);

            if (sizeof($font) > 1) {
                switch (strtolower($font[1])) {
                    case "Bold":
                        $weight = "bold";
                        break;
                    case "Oblique":
                        $style = "oblique";
                        break;
                    case "BoldOblique":
                        $weight = "bold";
                        $style = "oblique";
                        break;
                }
            }

            $declarations = "";
            $font = join("-", $font);
            $sniff = $proxy->sniff($_SERVER["HTTP_USER_AGENT"]);
            $served = $proxy->serve($font, $_SERVER["HTTP_USER_AGENT"]);

            if (sizeof($served) > 0) {
                $keys = array_keys($served);
                $declarations .= "@font-face {";
                $declarations .= "font-family: \"{$font}\";";

                if ($weight) {
                    $declarations .= "font-weight: {$weight};";
                }
                if ($style) {
                    $declarations .= "font-style: {$style};";
                }

                $type = $keys[0];
                $url = $served[$type];

                if ($sniff && strtolower($sniff["browser"]) == "ie") {
                    $declarations .= "src: url(\"{$url}\");";
                } else {
                    $declarations .= "src: url(\"{$url}\") format(\"{$type}\");";
                }

                $declarations .= "}";
            }

            header("Content-type: text/css");

            if ($declarations) {
                echo $declarations;
            } else {
                echo "/* no fonts to show */";
            }

            $this->willRenderLayoutView = false;
            $this->willRenderActionView = false;
        } else {
            header("Location: /public/{$path}/{$name}");
        }
    }

    /**
     * @before _secure, _admin
     */
    public function view()
    {
        $this->actionView->set("files", File::all());
    }

    /**
     * @before _secure, _admin
     */
    public function delete($id)
    {
        $file = File::first([
            "id = ?" => $id
        ]);

        if ($file) {
            $file->deleted = true;
            $file->save();
        }

        self::redirect("/public/files/view");
    }

    /**
     * @before _secure, _admin
     */
    public function undelete($id)
    {
        $file = File::first([
            "id = ?" => $id
        ]);

        if ($file) {
            $file->deleted = false;
            $file->save();
        }

        self::redirect("/public/files/view");
    }
}
