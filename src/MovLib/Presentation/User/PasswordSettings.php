<?php

/* !
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\User;

use \MovLib\Data\Delayed\Mailer;
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Data\User;
use \MovLib\Presentation\Email\User\PasswordChange as PasswordChangeEmail;
use \MovLib\Presentation\Form;
use \MovLib\Presentation\FormElement\InputPassword;
use \MovLib\Presentation\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\Alert;

/**
 * Allows a user to change her or his password.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PasswordSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\User\UserTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's form.
   *
   * @var \MovLib\Presentation\Form
   */
  private $form;

  /**
   * The input password form element for the new password.
   *
   * @var \MovLib\Presentation\FormElement\InputPassword
   */
  private $newPassword;

  /**
   * The input password form element for confirmation of the new password.
   *
   * @var \MovLib\Presentation\FormElement\InputPassword
   */
  private $newPasswordConfirm;


  // ------------------------------------------------------------------------------------------------------------------- Methods



  /**
   * Instantiate new user password settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   */
  public function __construct() {
    global $i18n, $session;

    $session
      ->checkAuthorization($i18n->t("You need to sign in to change your password."))
      ->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."))
    ;

    $this->init($i18n->t("Password Settings"));

    $this->newPassword = new InputPassword([
      "placeholder" => $i18n->t("Enter your new password"),
      "title"       => $i18n->t("Please enter your desired new password in this field."),
    ]);
    $this->newPassword->label = $i18n->t("New Password");
    $this->newPassword->help = "<a href='{$i18n->r("/user/reset-password")}'>{$i18n->t("Forgot your password?")}</a>";
    $this->newPassword->helpPopup = false;

    $this->newPasswordConfirm = new InputPassword([
      "placeholder" => $i18n->t("Enter your new password again"),
      "title"       => $i18n->t("Please enter your new password again in this field. we want to make sure that you don’t mistype this."),
    ]);
    $this->newPasswordConfirm->label = $i18n->t("Confirm Password");

    $this->form = new Form($this, [ $this->newPassword, $this->newPasswordConfirm ]);
    $this->form->attributes["action"] = $i18n->r("/user/password-settings");
    $this->form->attributes["autocomplete"] = "off";

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to request the password change after you filled out all fields."),
      "value" => $i18n->t("Request Password Change"),
    ]);

    if (isset($_GET["token"])) {
      $this->validateToken();
    }
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $info = new Alert("<p>{$i18n->t(
      "Choose a strong password, which is easy to remember but still hard to crack. To help you, we generated a " .
      "password from the most frequent words in American English:"
    )}</p><p><code>{$this->randomPassword()}</code></p>");
    $info->severity = Alert::SEVERITY_INFO;
    return "{$info}{$this->form}";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  public function validate() {
    global $i18n, $session;
    if ($this->newPassword->value != $this->newPasswordConfirm->value) {
      $this->newPassword->invalid();
      $this->newPasswordConfirm->invalid();
      $this->checkErrors([ $i18n->t("The confirmation password doesn’t match the new password, please try again.") ]);
    }
    else {
      $user = new User(User::FROM_ID, $session->userId);
      $user->setAuthenticationToken();
      DelayedMethodCalls::stack($user, "preparePasswordChange", [ $this->newPassword->value ]);
      Mailer::stack(new PasswordChangeEmail($user));

      // The request has been accepted, but further action is required to complete it.
      http_response_code(202);

      // Explain to the user where to find this further action to complete the request.
      $success = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($user->email) ]));
      $success->title = $i18n->t("Successfully Requested Password Change");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;

      // Also explain that this change is no immidiate action and that our system is still using the old password.
      $info = new Alert($i18n->t("You have to sign in with your old password until you’ve successfully confirmed your password change via the link we’ve just sent you."));
      $info->title = $i18n->t("Important!");
      $info->severity = Alert::SEVERITY_INFO;
      $this->alerts .= $info;
    }
    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  public function validateToken() {
    global $i18n, $session;
    $errors = null;
    $this->user = new User(User::FROM_ID, $session->userId);
    if (empty($_GET["token"]) || strlen($_GET["token"]) !== User::AUTHENTICATION_TOKEN_LENGTH) {
      $errors[] = $i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.");
    }
    elseif (!($data = $this->user->getTemporaryPasswordChangeData($_GET["token"]))) {
      $errors[] = $i18n->t("Your authentication token has expired, please fill out the form again.");
    }
    elseif ($data["id"] !== $this->user->id) {
      throw new UnauthorizedException($i18n->t("The authentication token is invalid, please sign in again and request a new token to change your password."));
    }
    if ($this->checkErrors($errors) === false) {
      $this->user->updatePassword($data["password"]);
      $success = new Alert($i18n->t("Your password was successfully changed to {0}.", [ $this->placeholder($data["password"]) ]));
      $success->title = $i18n->t("Password Changed Successfully");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;
    }
    return $this;
  }

  /**
   * Generate a user friendly password.
   *
   * @todo Is it really secure to use this? Or does it make it easier for dictionary attacks against our application
   *       because to possible wordlist is known? Email addresses are a secret at MovLib and an attacker doesn't know
   *       if a user has used one of the randomly generated words.
   * @link http://preshing.com/20110811/xkcd-password-generator
   * @link http://www.paulnoll.com/Books/Clear-English/English-3000-common-words.html
   * @return string
   *   A random four word password.
   */
  private function randomPassword() {
    $wordlist = [
      "ability", "able", "aboard", "about", "above", "accept", "accident", "according", "account", "accurate", "acres",
      "across", "act", "action", "active", "activity", "actual", "actually", "add", "addition", "additional",
      "adjective", "adult", "adventure", "advice", "affect", "afraid", "after", "afternoon", "again", "against", "age",
      "ago", "agree", "ahead", "aid", "air", "airplane", "alike", "alive", "all", "allow", "almost", "alone", "along",
      "aloud", "alphabet", "already", "also", "although", "am", "among", "amount", "ancient", "angle", "angry",
      "animal", "announced", "another", "answer", "ants", "any", "anybody", "anyone", "anything", "anyway", "anywhere",
      "apart", "apartment", "appearance", "apple", "applied", "appropriate", "are", "area", "arm", "army", "around",
      "arrange", "arrangement", "arrive", "arrow", "art", "article", "as", "aside", "ask", "asleep", "at", "ate",
      "atmosphere", "atom", "atomic", "attached", "attack", "attempt", "attention", "audience", "author", "automobile",
      "available", "average", "avoid", "aware", "away", "baby", "back", "bad", "badly", "bag", "balance", "ball",
      "balloon", "band", "bank", "bar", "bare", "bark", "barn", "base", "baseball", "basic", "basis", "basket", "bat",
      "battle", "be", "bean", "bear", "beat", "beautiful", "beauty", "became", "because", "become", "becoming", "bee",
      "been", "before", "began", "beginning", "begun", "behavior", "behind", "being", "believed", "bell", "belong",
      "below", "belt", "bend", "beneath", "bent", "beside", "best", "bet", "better", "between", "beyond", "bicycle",
      "bigger", "biggest", "bill", "birds", "birth", "birthday", "bit", "bite", "black", "blank", "blanket", "blew",
      "blind", "block", "blood", "blow", "blue", "board", "boat", "body", "bone", "book", "border", "born", "both",
      "bottle", "bottom", "bound", "bow", "bowl", "box", "boy", "brain", "branch", "brass", "brave", "bread", "break",
      "breakfast", "breath", "breathe", "breathing", "breeze", "brick", "bridge", "brief", "bright", "bring", "broad",
      "broke", "broken", "brother", "brought", "brown", "brush", "buffalo", "build", "building", "built", "buried",
      "burn", "burst", "bus", "bush", "business", "busy", "but", "butter", "buy", "by", "cabin", "cage", "cake", "call",
      "calm", "came", "camera", "camp", "can", "canal", "cannot", "cap", "capital", "captain", "captured", "car",
      "carbon", "card", "care", "careful", "carefully", "carried", "carry", "case", "cast", "castle", "cat", "catch",
      "cattle", "caught", "cause", "cave", "cell", "cent", "center", "central", "century", "certain", "certainly",
      "chain", "chair", "chamber", "chance", "change", "changing", "chapter", "character", "characteristic", "charge",
      "chart", "check", "cheese", "chemical", "chest", "chicken", "chief", "child", "children", "choice", "choose",
      "chose", "chosen", "church", "circle", "circus", "citizen", "city", "class", "classroom", "claws", "clay", "clean",
      "clear", "clearly", "climate", "climb", "clock", "close", "closely", "closer", "cloth", "clothes", "clothing",
      "cloud", "club", "coach", "coal", "coast", "coat", "coffee", "cold", "collect", "college", "colony", "color",
      "column", "combination", "combine", "come", "comfortable", "coming", "command", "common", "community", "company",
      "compare", "compass", "complete", "completely", "complex", "composed", "composition", "compound", "concerned",
      "condition", "congress", "connected", "consider", "consist", "consonant", "constantly", "construction", "contain",
      "continent", "continued", "contrast", "control", "conversation", "cook", "cookies", "cool", "copper", "copy",
      "corn", "corner", "correct", "correctly", "cost", "cotton", "could", "count", "country", "couple", "courage",
      "course", "court", "cover", "cow", "cowboy", "crack", "cream", "create", "creature", "crew", "crop", "cross",
      "crowd", "cry", "cup", "curious", "current", "curve", "customs", "cut", "cutting", "daily", "damage", "dance",
      "danger", "dangerous", "dark", "darkness", "date", "daughter", "dawn", "day", "dead", "deal", "dear", "death",
      "decide", "declared", "deep", "deeply", "deer", "definition", "degree", "depend", "depth", "describe", "desert",
      "design", "desk", "detail", "determine", "develop", "development", "diagram", "diameter", "did", "die", "differ",
      "difference", "different", "difficult", "difficulty", "dig", "dinner", "direct", "direction", "directly", "dirt",
      "dirty", "disappear", "discover", "discovery", "discuss", "discussion", "disease", "dish", "distance", "distant",
      "divide", "division", "do", "doctor", "does", "dog", "doing", "doll", "dollar", "done", "donkey", "door", "dot",
      "double", "doubt", "down", "dozen", "draw", "drawn", "dream", "dress", "drew", "dried", "drink", "drive", "driven",
      "driver", "driving", "drop", "dropped", "drove", "dry", "duck", "due", "dug", "dull", "during", "dust", "duty",
      "each", "eager", "ear", "earlier", "early", "earn", "earth", "easier", "easily", "east", "easy", "eat", "eaten",
      "edge", "education", "effect", "effort", "egg", "eight", "either", "electric", "electricity", "element",
      "elephant", "eleven", "else", "empty", "end", "enemy", "energy", "engine", "engineer", "enjoy", "enough", "enter",
      "entire", "entirely", "environment", "equal", "equally", "equator", "equipment", "escape", "especially",
      "essential", "establish", "even", "evening", "event", "eventually", "ever", "every", "everybody", "everyone",
      "everything", "everywhere", "evidence", "exact", "exactly", "examine", "example", "excellent", "except",
      "exchange", "excited", "excitement", "exciting", "exclaimed", "exercise", "exist", "expect", "experience",
      "experiment", "explain", "explanation", "explore", "express", "expression", "extra", "eye", "face", "facing",
      "fact", "factor", "factory", "failed", "fair", "fairly", "fall", "fallen", "familiar", "family", "famous", "far",
      "farm", "farmer", "farther", "fast", "fastened", "faster", "fat", "father", "favorite", "fear", "feathers",
      "feature", "fed", "feed", "feel", "feet", "fell", "fellow", "felt", "fence", "few", "fewer", "field", "fierce",
      "fifteen", "fifth", "fifty", "fight", "fighting", "figure", "fill", "film", "final", "finally", "find", "fine",
      "finest", "finger", "finish", "fire", "fireplace", "firm", "first", "fish", "five", "fix", "flag", "flame", "flat",
      "flew", "flies", "flight", "floating", "floor", "flow", "flower", "fly", "fog", "folks", "follow", "food", "foot",
      "football", "for", "force", "foreign", "forest", "forget", "forgot", "forgotten", "form", "former", "fort",
      "forth", "forty", "forward", "fought", "found", "four", "fourth", "fox", "frame", "free", "freedom", "frequently",
      "fresh", "friend", "friendly", "frighten", "frog", "from", "front", "frozen", "fruit", "fuel", "full", "fully",
      "fun", "function", "funny", "fur", "furniture", "further", "future", "gain", "game", "garage", "garden", "gas",
      "gasoline", "gate", "gather", "gave", "general", "generally", "gentle", "gently", "get", "getting", "giant",
      "gift", "girl", "give", "given", "giving", "glad", "glass", "globe", "go", "goes", "gold", "golden", "gone",
      "good", "goose", "got", "government", "grabbed", "grade", "gradually", "grain", "grandfather", "grandmother",
      "graph", "grass", "gravity", "gray", "great", "greater", "greatest", "greatly", "green", "grew", "ground", "group",
      "grow", "grown", "growth", "guard", "guess", "guide", "gulf", "gun", "habit", "had", "hair", "half", "halfway",
      "hall", "hand", "handle", "handsome", "hang", "happen", "happened", "happily", "happy", "harbor", "hard", "harder",
      "hardly", "has", "hat", "have", "having", "hay", "he", "headed", "heading", "health", "heard", "hearing", "heart",
      "heat", "heavy", "height", "held", "hello", "help", "helpful", "her", "herd", "here", "herself", "hidden", "hide",
      "high", "higher", "highest", "highway", "hill", "him", "himself", "his", "history", "hit", "hold", "hole",
      "hollow", "home", "honor", "hope", "horn", "horse", "hospital", "hot", "hour", "house", "how", "however", "huge",
      "human", "hundred", "hung", "hungry", "hunt", "hunter", "hurried", "hurry", "hurt", "husband", "ice", "idea",
      "identity", "if", "ill", "image", "imagine", "immediately", "importance", "important", "impossible", "improve",
      "in", "inch", "include", "including", "income", "increase", "indeed", "independent", "indicate", "individual",
      "industrial", "industry", "influence", "information", "inside", "instance", "instant", "instead", "instrument",
      "interest", "interior", "into", "introduced", "invented", "involved", "iron", "is", "island", "it", "its",
      "itself", "jack", "jar", "jet", "job", "join", "joined", "journey", "joy", "judge", "jump", "jungle", "just",
      "keep", "kept", "key", "kids", "kill", "kind", "kitchen", "knew", "knife", "know", "knowledge", "known", "label",
      "labor", "lack", "lady", "laid", "lake", "lamp", "land", "language", "large", "larger", "largest", "last", "late",
      "later", "laugh", "law", "lay", "layers", "lead", "leader", "leaf", "learn", "least", "leather", "leave",
      "leaving", "led", "left", "leg", "length", "lesson", "let", "letter", "level", "library", "lie", "life", "lift",
      "light", "like", "likely", "limited", "line", "lion", "lips", "liquid", "list", "listen", "little", "live",
      "living", "load", "local", "locate", "location", "log", "lonely", "long", "longer", "look", "loose", "lose",
      "loss", "lost", "lot", "loud", "love", "lovely", "low", "lower", "luck", "lucky", "lunch", "lungs", "lying",
      "machine", "machinery", "mad", "made", "magic", "magnet", "mail", "main", "mainly", "major", "make", "making",
      "man", "managed", "manner", "manufacturing", "many", "map", "mark", "market", "married", "mass", "massage",
      "master", "material", "mathematics", "matter", "may", "maybe", "me", "meal", "mean", "means", "meant", "measure",
      "meat", "medicine", "meet", "melted", "member", "memory", "men", "mental", "merely", "met", "metal", "method",
      "mice", "middle", "might", "mighty", "mile", "military", "milk", "mill", "mind", "mine", "minerals", "minute",
      "mirror", "missing", "mission", "mistake", "mix", "mixture", "model", "modern", "molecular", "moment", "money",
      "monkey", "month", "mood", "moon", "more", "morning", "most", "mostly", "mother", "motion", "motor", "mountain",
      "mouse", "mouth", "move", "movement", "movie", "moving", "mud", "muscle", "music", "musical", "must", "my",
      "myself", "mysterious", "nails", "name", "nation", "national", "native", "natural", "naturally", "nature", "near",
      "nearby", "nearer", "nearest", "nearly", "necessary", "neck", "needed", "needle", "needs", "negative", "neighbor",
      "neighborhood", "nervous", "nest", "never", "new", "news", "newspaper", "next", "nice", "night", "nine", "no",
      "nobody", "nodded", "noise", "none", "noon", "nor", "north", "nose", "not", "note", "noted", "nothing", "notice",
      "noun", "now", "number", "numeral", "nuts", "object", "observe", "obtain", "occasionally", "occur", "ocean", "of",
      "off", "offer", "office", "officer", "official", "oil", "old", "older", "oldest", "on", "once", "one", "only",
      "onto", "open", "operation", "opinion", "opportunity", "opposite", "or", "orange", "orbit", "order", "ordinary",
      "organization", "organized", "origin", "original", "other", "ought", "our", "ourselves", "out", "outer", "outline",
      "outside", "over", "own", "owner", "oxygen", "pack", "package", "page", "paid", "pain", "paint", "pair", "palace",
      "pale", "pan", "paper", "paragraph", "parallel", "parent", "park", "part", "particles", "particular",
      "particularly", "partly", "parts", "party", "pass", "passage", "past", "path", "pattern", "pay", "peace", "pen",
      "pencil", "people", "per", "percent", "perfect", "perfectly", "perhaps", "period", "person", "personal", "pet",
      "phrase", "physical", "piano", "pick", "picture", "pictured", "pie", "piece", "pig", "pile", "pilot", "pine",
      "pink", "pipe", "pitch", "place", "plain", "plan", "plane", "planet", "planned", "planning", "plant", "plastic",
      "plate", "plates", "play", "pleasant", "please", "pleasure", "plenty", "plural", "plus", "pocket", "poem", "poet",
      "poetry", "point", "pole", "police", "policeman", "political", "pond", "pony", "pool", "poor", "popular",
      "population", "porch", "port", "position", "positive", "possible", "possibly", "post", "pot", "potatoes", "pound",
      "pour", "powder", "power", "powerful", "practical", "practice", "prepare", "present", "president", "press",
      "pressure", "pretty", "prevent", "previous", "price", "pride", "primitive", "principal", "principle", "printed",
      "private", "prize", "probably", "problem", "process", "produce", "product", "production", "program", "progress",
      "promised", "proper", "properly", "property", "protection", "proud", "prove", "provide", "public", "pull", "pupil",
      "pure", "purple", "purpose", "push", "put", "putting", "quarter", "queen", "question", "quick", "quickly", "quiet",
      "quietly", "quite", "rabbit", "race", "radio", "railroad", "rain", "raise", "ran", "ranch", "range", "rapidly",
      "rate", "rather", "raw", "rays", "reach", "read", "reader", "ready", "real", "realize", "rear", "reason", "recall",
      "receive", "recent", "recently", "recognize", "record", "red", "refer", "refused", "region", "regular", "related",
      "relationship", "religious", "remain", "remarkable", "remember", "remove", "repeat", "replace", "replied", "report",
      "represent", "require", "research", "respect", "rest", "result", "return", "review", "rhyme", "rhythm", "rice",
      "rich", "ride", "riding", "right", "ring", "rise", "rising", "river", "road", "roar", "rock", "rocket", "rocky",
      "rod", "roll", "roof", "room", "root", "rope", "rose", "rough", "round", "route", "row", "rubbed", "rubber",
      "rule", "ruler", "run", "running", "rush", "sad", "saddle", "safe", "safety", "said", "sail", "sale", "salmon",
      "salt", "same", "sand", "sang", "sat", "satellites", "satisfied", "save", "saved", "saw", "say", "scale", "scared",
      "scene", "school", "science", "scientific", "scientist", "score", "screen", "sea", "search", "season", "seat",
      "second", "secret", "section", "see", "seed", "seeing", "seems", "seen", "seldom", "select", "selection", "sell",
      "send", "sense", "sent", "sentence", "separate", "series", "serious", "serve", "service", "sets", "setting",
      "settle", "settlers", "seven", "several", "shade", "shadow", "shake", "shaking", "shall", "shallow", "shape",
      "share", "sharp", "she", "sheep", "sheet", "shelf", "shells", "shelter", "shine", "shinning", "ship", "shirt",
      "shoe", "shoot", "shop", "shore", "short", "shorter", "shot", "should", "shoulder", "shout", "show", "shown",
      "shut", "sick", "sides", "sight", "sign", "signal", "silence", "silent", "silk", "silly", "silver", "similar",
      "simple", "simplest", "simply", "since", "sing", "single", "sink", "sister", "sit", "sitting", "situation", "six",
      "size", "skill", "skin", "sky", "slabs", "slave", "sleep", "slept", "slide", "slight", "slightly", "slip",
      "slipped", "slope", "slow", "slowly", "small", "smaller", "smallest", "smell", "smile", "smoke", "smooth", "snake",
      "snow", "so", "soap", "social", "society", "soft", "softly", "soil", "solar", "sold", "soldier", "solid",
      "solution", "solve", "some", "somebody", "somehow", "someone", "something", "sometime", "somewhere", "son", "song",
      "soon", "sort", "sound", "source", "south", "southern", "space", "speak", "special", "species", "specific",
      "speech", "speed", "spell", "spend", "spent", "spider", "spin", "spirit", "spite", "split", "spoken", "sport",
      "spread", "spring", "square", "stage", "stairs", "stand", "standard", "star", "stared", "start", "state",
      "statement", "station", "stay", "steady", "steam", "steel", "steep", "stems", "step", "stepped", "stick", "stiff",
      "still", "stock", "stomach", "stone", "stood", "stop", "stopped", "store", "storm", "story", "stove", "straight",
      "strange", "stranger", "straw", "stream", "street", "strength", "stretch", "strike", "string", "strip", "strong",
      "stronger", "struck", "structure", "struggle", "stuck", "student", "studied", "studying", "subject", "substance",
      "success", "successful", "such", "sudden", "suddenly", "sugar", "suggest", "suit", "sum", "summer", "sun",
      "sunlight", "supper", "supply", "support", "suppose", "sure", "surface", "surprise", "surrounded", "swam", "sweet",
      "swept", "swim", "swimming", "swing", "swung", "syllable", "symbol", "system", "table", "tail", "take", "taken",
      "tales", "talk", "tall", "tank", "tape", "task", "taste", "taught", "tax", "tea", "teach", "teacher", "team",
      "tears", "teeth", "telephone", "television", "tell", "temperature", "ten", "tent", "term", "terrible", "test",
      "than", "thank", "that", "thee", "them", "themselves", "then", "theory", "there", "therefore", "these", "they",
      "thick", "thin", "thing", "think", "third", "thirty", "this", "those", "thou", "though", "thought", "thousand",
      "thread", "three", "threw", "throat", "through", "throughout", "throw", "thrown", "thumb", "thus", "thy", "tide",
      "tie", "tight", "tightly", "till", "time", "tin", "tiny", "tip", "tired", "title", "to", "tobacco", "today",
      "together", "told", "tomorrow", "tone", "tongue", "tonight", "too", "took", "tool", "top", "topic", "torn",
      "total", "touch", "toward", "tower", "town", "toy", "trace", "track", "trade", "traffic", "trail", "train",
      "transportation", "trap", "travel", "treated", "tree", "triangle", "tribe", "trick", "tried", "trip", "troops",
      "tropical", "trouble", "truck", "trunk", "truth", "try", "tube", "tune", "turn", "twelve", "twenty", "twice",
      "two", "type", "typical", "uncle", "under", "underline", "understanding", "unhappy", "union", "unit", "universe",
      "unknown", "unless", "until", "unusual", "up", "upon", "upper", "upward", "us", "use", "useful", "using", "usual",
      "usually", "valley", "valuable", "value", "vapor", "variety", "various", "vast", "vegetable", "verb", "vertical",
      "very", "vessels", "victory", "view", "village", "visit", "visitor", "voice", "volume", "vote", "vowel", "voyage",
      "wagon", "wait", "walk", "wall", "want", "war", "warm", "warn", "was", "wash", "waste", "watch", "water", "wave",
      "way", "we", "weak", "wealth", "wear", "weather", "week", "weigh", "weight", "welcome", "well", "went", "were",
      "west", "western", "wet", "whale", "what", "whatever", "wheat", "wheel", "when", "whenever", "where", "wherever",
      "whether", "which", "while", "whispered", "whistle", "white", "who", "whole", "whom", "whose", "why", "wide",
      "widely", "wife", "wild", "will", "willing", "win", "wind", "window", "wing", "winter", "wire", "wise", "wish",
      "with", "within", "without", "wolf", "women", "won", "wonder", "wonderful", "wood", "wooden", "wool", "word",
      "wore", "work", "worker", "world", "worried", "worry", "worse", "worth", "would", "wrapped", "write", "writer",
      "writing", "written", "wrong", "wrote", "yard", "year", "yellow", "yes", "yesterday", "yet", "you", "young",
      "younger", "your", "yourself", "youth", "zero", "zoo"
    ];
    $wordCount = count($wordlist) - 1;
    $password = [];
    for ($i = 0; $i < 4; ++$i) {
      $password[] = $wordlist[mt_rand(0, $wordCount)];
    }
    return implode(" ", $password);
  }

}
