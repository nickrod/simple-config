<?php

//

declare(strict_types=1);

//

namespace simpleconfig;

//

class SimpleConfig
{
  // pdo connection

  private $pdo;

  // pdo options

  private $pdo_options = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES => false
  ];

  // dsn

  private $dsn;

  // db username

  private $username;

  // db password

  private $password;

  // settings file

  private $settings_file;

  // site url

  private $site_url;

  // site domain

  private $site_domain;

  // site currency

  private $site_currency;

  // site language

  private $site_language;

  // constructor

  public function __construct(array $column = [])
  {
    if (isset($column['pdo_options']))
    {
      $this->setPdoOptions($column['pdo_options']);
    }

    //

    if (isset($column['settings_file']))
    {
      $this->setSettingsFile($column['settings_file']);
    }
    else
    {
      if (isset($column['dsn']))
      {
        $this->setDsn($column['dsn']);
      }

      //

      if (isset($column['username']))
      {
        $this->setUsername($column['username']);
      }

      //

      if (isset($column['password']))
      {
        $this->setPassword($column['password']);
      }

      //

      if (isset($column['site_url']))
      {
        $this->setSiteUrl($column['site_url']);
      }

      //

      if (isset($column['site_domain']))
      {
        $this->setSiteDomain($column['site_domain']);
      }

      //

      if (isset($column['site_currency']))
      {
        $this->setSiteCurrency($column['site_currency']);
      }

      //

      if (isset($column['site_language']))
      {
        $this->setSiteLanguage($column['site_language']);
      }
    }
  }

  // getters

	public function getPdo(): \PDO
  {
    return $this->pdo;
	}

  //

	public function getSiteUrl(): string
  {
    return $this->site_url;
	}

  //

	public function getSiteDomain(): string
  {
    return $this->site_domain;
	}

  //

	public function getSiteCurrency(): string
  {
    return $this->site_currency;
	}

  //

	public function getSiteLanguage(): string
  {
    return $this->site_language;
	}

  // setters

	public function setDsn(string $dsn): void
  {
    $this->dsn = $dsn;
  }

  //

	public function setUsername(string $username): void
  {
    $this->username = $username;
  }

  //

	public function setPassword(string $password): void
  {
    $this->password = $password;
    $this->setPdo();
  }

  //

	public function setSiteUrl(string $site_url): void
  {
    if (!filter_var($site_url, FILTER_VALIDATE_URL))
    {
      throw new \InvalidArgumentException('Site url is invalid: ' . $site_url);
    }
    else
    {
      $this->site_url = $site_url;
    }
  }

  //

	public function setSiteDomain(string $site_domain): void
  {
    if (!filter_var($site_domain, FILTER_VALIDATE_DOMAIN))
    {
      throw new \InvalidArgumentException('Site domain is invalid: ' . $site_domain);
    }
    else
    {
      $this->site_domain = $site_domain;
    }
  }

  //

	public function setSiteCurrency(string $site_currency): void
  {
    $length = strlen($site_currency);

    //

    if ($length !== 3 || $length !== 4)
    {
      throw new \InvalidArgumentException('Site currency is invalid: ' . $site_currency);
    }
    else
    {
      $this->site_currency = $site_currency;
    }
  }

  //

	public function setSiteLanguage(string $site_language): void
  {
    $length = strlen($site_language);

    //

    if ($length !== 2)
    {
      throw new \InvalidArgumentException('Site language is invalid: ' . $site_language);
    }
    else
    {
      $this->site_language = $site_language;
    }
  }

  //

	public function setSettingsFile(string $settings_file): void
  {
    if (!file_exists($settings_file))
    {
      throw new \InvalidArgumentException('Settings file does not exist: ' . $settings_file);
    }
    else
    {
      $this->settings_file = $settings_file;
      $this->loadSettingsFile();
    }
	}

  //

	public function setPdoOptions(array $pdo_options): void
  {
    $this->pdo_options = $pdo_options;
	}

  //

	private function setPdo(): void
  {
    try
    {
      $this->pdo = new \PDO($this->dsn, $this->username, $this->password, $this->pdo_options);
    }
    catch (\PDOException $e)
    {
      throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
	}

  //

  private function loadSettingsFile(): void
  {
    if (!$settings = parse_ini_file($this->settings_file, true))
    {
      throw new \InvalidArgumentException('Settings file cannot be parsed: ' . $this->settings_file);
    }
    else
    {
      if (!isset($settings['database']['driver']) || !isset($settings['database']['host']) || !isset($settings['database']['port']) || !isset($settings['database']['dbname']) || !isset($settings['database']['charset']) || !isset($settings['database']['username']) || !isset($settings['database']['password']) || !isset($settings['site']['url']) || !isset($settings['site']['domain']) || !isset($settings['site']['currency']) || !isset($settings['site']['language']))
      {
        throw new \InvalidArgumentException('Required settings are missing: driver, host, port, dbname, charset, username, password, url, domain, currency, language.');
      }
      else
      {
        $this->setDsn($settings['database']['driver'] . ':host=' . $settings['database']['host'] . ';port=' . $settings['database']['port'] . ';dbname=' . $settings['database']['dbname'] . (($settings['database']['driver'] === 'pgsql') ? ';options=\'-c client_encoding=' . $settings['database']['charset'] . '\'' : ';charset=' . $settings['database']['charset']));
        $this->setUsername($settings['database']['username']);
        $this->setPassword($settings['database']['password']);
        $this->setSiteUrl($settings['site']['url']);
        $this->setSiteDomain($settings['site']['domain']);
        $this->setSiteCurrency($settings['site']['currency']);
        $this->setSiteLanguage($settings['site']['language']);
        $this->setPdo();
      }
    }
  }
}
