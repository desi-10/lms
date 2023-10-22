const answers = [
  {
    id: 1,
    answer: "baby boy",
  },
  {
    id: 2,
    answer: "Lorem ipsum dolor sit amet, consectetur adipisicing elit",
  },
  {
    id: 3,
    answer:
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsum repellat ipsam adipisci dolore odit ratione architecto tempore vitae, eaque",
  },
  {
    id: 4,
    answer:
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsum repellat ipsam adipisci dolore odit ratione architecto tempore vitae, eaque",
  },
];

const QuizPage = () => {
  return (
    <section className="h-screen">
      <h1 className="text-center p-5 lg:py-10 lg:text-2xl">DBMS Quiz</h1>
      <main className="grid lg:grid-cols-2 lg:h-[80%]">
        <article className="border-r px-5 mb-3 lg:mb-0">
          <h2 className="font-bold lg:text-3xl text-black mb-5">00:00:00</h2>
          <h3 className="text-black lg:text-2xl mb-3">Question</h3>
          <p className="text-sm lg:text-base">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsum
            repellat ipsam adipisci dolore odit ratione architecto tempore
            vitae, eaque, nulla excepturi pariatur id, quod saepe sit. Neque
            debitis labore voluptate.
          </p>
        </article>

        <div className="px-5 mb-5 overflow-y-scroll">
          <div className="flex justify-between items-center mb-5 sticky top-0 z-20">
            <button className="py-2 px-6 text-white text-sm lg:text-base bg-blue-700 rounded">
              Prev
            </button>

            <button className="py-2 px-6 text-white text-sm lg:text-base bg-blue-700 rounded">
              Next
            </button>
          </div>

          <article className="space-y-5 text-sm lg:text-base">
            {answers.map((answer) => {
              return (
                <div className="relative w-full border p-5 lg:p-10 rounded-lg">
                  <p className="relative text-black z-10">{answer.answer}</p>
                  <p className="absolute font-bold lg:text-2xl right-5 top-[50%] translate-y-[-50%] translate-x-[-50%]">
                    {answer.id}
                  </p>
                </div>
              );
            })}
          </article>
        </div>
      </main>
    </section>
  );
};

export default QuizPage;
