define(['jquery', 'core/log', 'qtype_yconrunner/ace_wrapper'], function ($, log, acePromise) {
    return {
        init: function (params) {
            $(document).ready(function () {
                var questions = document.querySelectorAll('.que');

                questions.forEach(function(question) {
                    // Проверяем, есть ли в этом вопросе кнопка с текстом "Проверить"
                    var verifyButton = question.querySelector('button');
                    if (verifyButton && verifyButton.textContent.trim() === 'Проверить') {
                        // Если есть, ищем кнопку "Check" в том же вопросе и скрываем её
                        var checkButton = question.querySelector('.im-controls');
                        if (checkButton) {
                            checkButton.style.display = 'none';
                        }
                    }
                });

                // Загрузка данных из API
                fetch(`http://localhost:3000/api/contests/${params.contestid}/problems/${params.submissionid}/statement`)
                    .then(response => response.text())
                    .then(data => {
                        log.debug('Received problem statement:', data);
                        $('#question-text').html(data);
                    })
                    .catch(error => log.error('Ошибка при получении описания проблемы', error));

                // Использование ACE из нашего обёрточного модуля
                acePromise.then(function (ace) {
                    log.debug('ACE loaded successfully');
                    var editor = ace.edit("editor");
                    editor.setTheme("ace/theme/monokai-light");
                    editor.session.setMode("ace/mode/python");
                    editor.setOptions({
                        fontSize: "18px"
                    });

                    $('#language-select').change(function () {
                        var language = $(this).val();
                        switch (language) {
                            case 'python':
                                editor.session.setMode("ace/mode/python");
                                break;
                            case 'cpp':
                                editor.session.setMode("ace/mode/c_cpp");
                                break;
                            case 'java':
                                editor.session.setMode("ace/mode/java");
                                break;
                            case 'csharp':
                                editor.session.setMode("ace/mode/csharp");
                                break;
                        }
                    });

                    $('#file-upload').after('<button type="button" id="remove-file" class="btn btn-danger ml-2">Удалить файл</button>');

                    // Обработчик для удаления файла
                    $('#remove-file').click(function () {
                        $('#file-upload').val(null);
                        window.onbeforeunload = null;
                    });

                    $('#submit-button').click(function () {
                        // Удаляем все обработчики событий на уход со страницы
                        window.onbeforeunload = null;

                        let answer = editor.getValue();
                        let language = $('#language-select').val();
                        let extension;
                        let commentPrefix;

                        // Определение расширения файла и префикса комментария
                        switch (language) {
                            case 'python':
                                extension = '.py';
                                commentPrefix = '#';
                                break;
                            case 'cpp':
                            case 'java':
                            case 'csharp':
                                extension = language === 'cpp' ? '.cpp' : (language === 'java' ? '.java' : '.cs');
                                commentPrefix = '//';
                                break;
                        }

                        // Генерация случайного числа и добавление его в качестве комментария
                        let randomNumber = Math.floor(Math.random() * 1000000);
                        let randomComment = `${commentPrefix} Random number: ${randomNumber}\n`;
                        answer = randomComment + answer;

                        let contestid = params.contestid;
                        let fileInput = $('#file-upload')[0];
                        let formData = new FormData();

                        if (fileInput.files.length > 0) {
                            // Если файл загружен, добавляем комментарий к содержимому файла
                            let file = fileInput.files[0];
                            let reader = new FileReader();
                            reader.onload = function (e) {
                                let content = e.target.result;
                                let modifiedContent = randomComment + content;
                                formData.append('file', new Blob([modifiedContent], {type: 'text/plain'}), 'main' + extension);
                                sendRequest(formData, language, contestid);
                            };
                            reader.readAsText(file);
                        } else {
                            // Если файл не загружен, используем текст из редактора
                            let file = new Blob([answer], {type: 'text/plain'});
                            formData.append('file', file, 'main' + extension);
                            sendRequest(formData, language, contestid);
                        }
                    });

                    function sendRequest(formData, language, contestid) {
                        formData.append('compiler', language === 'python' ? 'python3' : (language === 'cpp' ? 'gcc_cpp20' : (language === 'java' ? 'javac' : 'mcs')));
                        formData.append('problem', 'A'); // Пример использования submissionid в качестве problem

                        const myHeaders = new Headers();
                        myHeaders.append("Authorization", "OAuth y0_AgAEA7qkKGeUAAv6MwAAAAEIAX7dAABWL6qZvrJOUJvQhMBpgC27VCKHrg");

                        const requestOptions = {
                            method: "POST",
                            headers: myHeaders,
                            body: formData,
                            redirect: "follow"
                        };

                        fetch(`http://localhost:3000/api/contests/${contestid}/submissions`, requestOptions)
                            .then(response => response.json())
                            .then(result => {
                                log.debug(result);

                                // Получение вердикта из ответа
                                let verdict = result.verdict;

                                // Отображение сообщения пользователю
                                $('#result-message').text(verdict).show();

                                // Отправка результата на сервер Moodle для оценки
                                $.ajax({
                                    url: M.cfg.wwwroot + '/question/type/yconrunner/grade_response.php',
                                    method: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({result: verdict === 'OK' ? 1 : 0, attemptid: params.attemptid}),
                                    success: function (response) {
                                        log.debug('Оценка успешно сохранена', response);
                                        $('#file-upload').val(null); // Удаление файла из прикрепления
                                        window.onbeforeunload = null;
                                    },
                                    error: function (error) {
                                        log.error('Ошибка при сохранении оценки:', error);
                                        $('#file-upload').val(null); // Удаление файла из прикрепления
                                        window.onbeforeunload = null;
                                    }
                                });
                            })
                            .catch(error => {
                                log.debug(error);
                                $('#result-message').text('Произошла ошибка при отправке решения.').show();
                                // Отправка результата на сервер Moodle для оценки
                                $.ajax({
                                    url: M.cfg.wwwroot + '/question/type/yconrunner/grade_response.php',
                                    method: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({result: 0, attemptid: params.attemptid}),
                                    success: function (response) {
                                        log.debug('Оценка успешно сохранена', response);
                                        $('#file-upload').val(null); // Удаление файла из прикрепления
                                        window.onbeforeunload = null;
                                    },
                                    error: function (error) {
                                        log.error('Ошибка при сохранении оценки:', error);
                                        $('#file-upload').val(null); // Удаление файла из прикрепления
                                        window.onbeforeunload = null;
                                    }
                                });
                            });
                    }
                }).catch(function (error) {
                    log.error('Failed to load ACE:', error);
                });
            });
        }
    };
});
