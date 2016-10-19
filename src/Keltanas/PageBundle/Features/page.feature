Feature: page
    Check, that pages has opened

    @mink:symfony2
    Scenario: Check opening public pages
        Given I go to "/"
        Then the response status code should be 200
         And I should see "Шпаргалки Просто Программиста" in the "h1" element
         And I should see "Облако меток" in the "h3" element
         And I should see "Полезные ссылки"
        When I follow "Обо мне"
        Then the response status code should be 200
        And I should see "Обо мне" in the "h1" element

    @mink:symfony2
    Scenario: Check manage posts
        Given I go to "/"
        When I follow "Login"
        Then the response status code should be 200

        When I fill in the following:
            | Имя пользователя  | keltanas |
            | Пароль            | 123456 |
         And I press "Войти"
        Then the response status code should be 200
         And I should see "Новый пост"
         And I should see "Все посты"
         And I should see "keltanas"
         And I should see "Logout"

        When I follow "Новый пост"
        Then the response status code should be 200
         And I should see "Создание поста" in the "h1" element

        When I fill in the following:
            | Титул | Ololo |
            | Содержание в формате MarkDown | Trololo |
         And I press "Создать"
        Then the response status code should be 200
         And I should see "Ololo" in the "h1" element
         And I should see "Trololo"

        When I follow "Править"
        Then the response status code should be 200
         And I should see "Редактирование поста Ololo" in the "h1" element

        When I fill in the following:
          | Титул | Foo |
          | Содержание в формате MarkDown | Bar |
         And I press "Сохранить"

        Then the response status code should be 200
        And I should see "Foo" in the "h1" element
        And I should see "Bar"

      When I follow "Удалить"
        Then the response status code should be 200
         And I should see "Удаление" in the "h1" element
         And I should see "Действительно хотите удалить?"
        When I press "Удалить"
        Then I should see "Список постов" in the "h1" element
         And I should not see "Ololo"
         And I should not see "Foo"
