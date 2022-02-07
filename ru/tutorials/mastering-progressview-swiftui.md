В процессе написания приложения, у нас появляются методы, которые выполняют фоновую работу, будь то работа с сетью или обработка данных.


## Неопределенный прогресс

Для того, чтобы создать бесконечный прогресс загрузки, поместим `ProgressView()` в нашу вью:

```swift
//filename: ContentView.swift

struct ContentView: View {
  var body: some View {
    VStack(spacing: 40) {
      ProgressView()
      Divider()
      ProgressView("Loading")
  }
}
```


![indeterminate activity indicator](image)

По умолчанию SwiftUI определяет такой индикатор активности, которым обозначают некоторую работу в фоне.


## Определенный прогресс

В отличии от неопределенного, мы можем показать прогресс, который выполняется и имеет явный индикатор.
Для этого инициализируем вью способом ниже:

```swift
struct ContentView: View {
  var body: some View {
    ProgressView(value: 50, total: 150)
      .tint(.gray)
      .padding(.horizontal)
  }
}
```

![determinate activity indicator](image)

Таким образом можно показать пользователю, что загрузка данных выполняется в зависимости от размера файла.

## Дизайн

SwiftUI предоставляет протокол `ProgressViewStyle`, который позволяет создавать собственный дизайн для `ProgressView`.
