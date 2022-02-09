В процессе написания приложения, у нас появляются методы, которые выполняют фоновую работу, будь то работа с сетью или обработка данных. Для того, чтобы обозначить такую работу, нам поможет `ProgressView` — вью, которое показывает ход выполнения задачи/работы.


## Неопределенный прогресс

Для того, чтобы создать индикатор загрузки, поместим `ProgressView()` в нашу вью:

```swift
struct ContentView: View {
  var body: some View {
    VStack(spacing: 40) {
      ProgressView()
      Divider()
      ProgressView("Loading")
      .tint(.pink)
  }
}
```


[Indeterminate activity indicator](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/indeterminate_activity_indicator.mov)

По умолчанию SwiftUI определяет вращающийся бар загрузки (спиннер), которым обозначают некоторую работу в фоне.
Обратите внимание, что с помощью модификатора `.tint()` можно изменить цвет бара.
Теперь посмотрим на другой способ инициализирования.


## Определенный прогресс

В отличии от неопределенного, мы можем показать прогресс, который имеет явный индикатор.
Для этого инициализируем вью способом ниже:

```swift
struct ContentView: View {
  let totalProgress: Double = 100
  @State private var progress = 0.0

  var body: some View {
    VStack(spacing: 40) {
      currentTextProgress
      
      ProgressView(value: progress, total: totalProgress)
        .padding(.horizontal, 40)
      
      loadResetButtons
    }
  }
}

extension ContentView {
  private var currentTextProgress: Text {
      switch progress {
        case 5..<totalProgress: return Text("Current progress: \(Int(progress))%")
        case totalProgress...: return Text("Loading complete")
        default: return Text("Start loading")
      }
    }
    
  private var loadResetButtons: some View {
    HStack(spacing: 20) {
      Button("Load more") {
        withAnimation { progress += Double.random(in: 5...20) }
      }
      .disabled(!progress.isLessThanOrEqualTo(totalProgress))
      
      Button(role: .destructive) { 
        progress = 0
      } label: { 
        Text("Reset")
      }
      .tint(.red)
      .disabled(progress.isZero)
    }
    .buttonStyle(.bordered)
  }
}
```

[Determinate activity indicator](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/determinate_activity_indicator.mov)


По нажатию кнопки `Load more` начинается загрузка, текст показывает текущий прогресс, а кнопка `Reset` становится доступной для нажатия и сброса прогресса. По достижению загрузки, текст на экране сообщает о завершении загрузки и кнопка `Load more` становится неактивной.

Для симуляции прогресса, я покажу еще один пример с таймером.

```swift
// filename: TimerProgressView.swift

struct TimerProgressView: View {
  let timer = Timer
      .publish(every: 0.05, on: .main, in: .common)
      .autoconnect()
      
  let downloadTotal: Double = 100
  @State private var progress: Double = 0
    
  var body: some View {
    VStack(spacing: 40) {
      Text("Downloading: \(Int(progress))%")
     
      ProgressView(value: progress, total: downloadTotal)
        .tint(progress < 50 ? .pink : .green)
        .padding(.horizontal)
        .onReceive(timer) { _ in
          if progress < downloadTotal { progress += 1 }
      }
    }
  }
}
```

[Timer progress](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/timer_progress.mov)


В данном примере, мы вызываем событие многократно, используя таймер.
Код для создания таймера выглядит так:

```swift
let timer = Timer.publish(every: 0.05, on: .main, in: .common).autoconnect()
```

Поясню некоторые моменты:

1. Таймер срабатывает каждые 0.05 секунд, что равняется 50 миллисекунд.
2. Таймер должен работать в главном потоке.
3. Таймер должен работать в общем цикле(common run loop). Run loop позволяет обрабатывать работающий
код, когда пользователь делает что-либо, например нажимает на кнопку.
4. Таймер подключается немедленно и начинается сразу отчитывать время.

Когда `progress` достигнет `downloadTotal` значения, таймер остановится.
При достижении 50% загрузки, индикатор меняет цвет на зеленый.

При таком объявлении, `ProgressView` выглядит как полоса загрузки, которая заполняется слева направо.
Так можно показать пользователю, что загрузка данных выполняется в зависимости от размера файла.


Описание метода `publish` доступно в [документации Apple](https://developer.apple.com/documentation/foundation/timer/3329589-publish).

Больше инициализаторов можно найти в документации Xcode или [на сайте](https://developer.apple.com/documentation/swiftui/progressview).

![Documentation SwiftUI ProgressView](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/progressview_init.png)


## Дизайн


SwiftUI предоставляет протокол `ProgressViewStyle`, который позволяет создавать собственный дизайн для `ProgressView`.
Например, вы можете настроить вид и взаимодействие прогресса, создав стиль, который наследуется от протокола `ProgressViewStyle`.


Для этого объявим структуру `RoundedProgressViewStyle`, которая наследуется от протокола `ProgressViewStyle` и содержит метод `makeBody()`, принимающий параметр конфигурации для нашего стиля.

```swift
struct RoundedProgressViewStyle: ProgressViewStyle {
  let color: Color
  
  func makeBody(configuration: Configuration) -> some View {
    let fractionCompleted = configuration.fractionCompleted ?? 0
    
    RoundedRectangle(cornerRadius: 18)
      .frame(width: CGFloat(fractionCompleted) * 200, height: 22)
      .foregroundColor(color)
      .padding(.horizontal)
  }
}
```

Далее вернемся к `TimerProgressView.swift` и передадим `RoundedProgressViewStyle(color: .cyan)` в модификатор `.progressViewStyle()`.

Теперь код выглядит так:

```swift
struct TimerProgressView: View {
  let timer = Timer
      .publish(every: 0.05, on: .main, in: .common)
      .autoconnect()
      
  let downloadTotal: Double = 100
  @State private var progress: Double = 0
    
  var body: some View {
    VStack(spacing: 40) {
      Text("Downloading: \(Int(progress))%")
      
      ProgressView(value: progress, total: downloadTotal)
        .onReceive(timer) { _ in
            if progress < downloadTotal { progress += 1 }
        }
        .progressViewStyle(
            RoundedProgressViewStyle(color: .cyan)
        )
    }
  }
}
```

Обратите внимание, что прогресс начинается не слева направо, а с середины в противоположные стороны.

[RoundedProgressViewStyle](mov)
